<?php

namespace App\Http\Controllers\Smartmd;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Laravel\Facades\Image;
use InvalidArgumentException;
use RuntimeException;

/**
 * Controller for handling image uploads in the Smartmd editor.
 * 
 * This controller provides secure image upload functionality with:
 * - File validation and size limits
 * - Automatic image resizing and optimization
 * - Configurable upload paths and quality settings
 * - Comprehensive error handling
 * 
 * @package App\Http\Controllers\Smartmd
 * @author noisywinds
 * @since 2.0.0
 */
class UploadController extends Controller
{
    /**
     * Maximum allowed image width in pixels.
     */
    protected const MAX_IMAGE_WIDTH = 1200;

    /**
     * Image compression quality (1-100).
     */
    protected const DEFAULT_QUALITY = 80;

    /**
     * Handle image upload and processing.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function imSave(Request $request): JsonResponse
    {
        try {
            $this->validateImageUpload($request);
            
            $uploadedFile = $request->file('image');
            if (!$uploadedFile instanceof UploadedFile) {
                return $this->errorResponse('No valid image file received', 400);
            }

            $result = $this->processImage($uploadedFile);
            
            return response()->json([
                'success' => true,
                'path' => $result['url'],
                'size' => $result['dimensions'],
                'message' => 'Image uploaded successfully',
                'file_info' => [
                    'original_name' => $uploadedFile->getClientOriginalName(),
                    'size' => $result['file_size'],
                    'mime_type' => $uploadedFile->getMimeType(),
                ]
            ]);
            
        } catch (ValidationException $e) {
            return $this->errorResponse(
                $e->validator->errors()->first(), 
                422
            );
            
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 400);
            
        } catch (RuntimeException $e) {
            return $this->errorResponse(
                'Image processing failed: ' . $e->getMessage(), 
                500
            );
            
        } catch (\Exception $e) {
            return $this->errorResponse(
                'An unexpected error occurred during image upload', 
                500
            );
        }
    }

    /**
     * Validate the image upload request.
     *
     * @param Request $request
     * @throws ValidationException
     */
    protected function validateImageUpload(Request $request): void
    {
        $maxSize = config('smartmd.image.max_size', 4096);
        $allowedExtensions = config('smartmd.image.allowed_extensions', 
            ['jpg', 'jpeg', 'png', 'gif', 'webp']
        );
        
        $validator = Validator::make($request->all(), [
            'image' => [
                'required',
                'file',
                'image',
                "max:{$maxSize}",
                'mimes:' . implode(',', $allowedExtensions),
                'dimensions:min_width=1,min_height=1,max_width=4000,max_height=4000'
            ]
        ], [
            'image.required' => 'Please select an image to upload',
            'image.image' => 'The uploaded file must be a valid image',
            'image.max' => "The image size must not exceed {$maxSize} KB",
            'image.mimes' => 'The image must be of type: ' . implode(', ', $allowedExtensions),
            'image.dimensions' => 'The image dimensions are invalid',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Process and optimize the uploaded image.
     *
     * @param UploadedFile $file
     * @return array<string, mixed>
     * @throws RuntimeException
     */
    protected function processImage(UploadedFile $file): array
    {
        $filename = $this->generateSecureFilename($file);
        $uploadPath = $this->getUploadPath();
        $fullPath = $uploadPath . '/' . $filename;
        
        // Ensure upload directory exists
        if (!is_dir($uploadPath) && !mkdir($uploadPath, 0755, true) && !is_dir($uploadPath)) {
            throw new RuntimeException('Failed to create upload directory');
        }

        try {
            $image = Image::read($file->getPathname());
            $originalWidth = $image->width();
            $originalHeight = $image->height();
            
            $processedDimensions = $this->calculateOptimalDimensions(
                $originalWidth, 
                $originalHeight
            );
            
            // Resize if necessary
            if ($processedDimensions['width'] !== $originalWidth || 
                $processedDimensions['height'] !== $originalHeight) {
                $image->resize(
                    $processedDimensions['width'], 
                    $processedDimensions['height']
                );
            }
            
            $quality = config('smartmd.image.quality', self::DEFAULT_QUALITY);
            $image->save($fullPath, $quality);
            
            return [
                'url' => $this->getPublicUrl($filename),
                'dimensions' => $processedDimensions,
                'file_size' => filesize($fullPath) ?: 0,
            ];
            
        } catch (\Exception $e) {
            throw new RuntimeException('Image processing failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate a secure filename for the uploaded image.
     *
     * @param UploadedFile $file
     * @return string
     */
    protected function generateSecureFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $hash = $file->hashName();
        
        // If hashName doesn't include extension, add it
        if (!str_ends_with($hash, '.' . $extension)) {
            $hash = pathinfo($hash, PATHINFO_FILENAME) . '.' . $extension;
        }
        
        return $hash;
    }

    /**
     * Calculate optimal image dimensions based on max width constraint.
     *
     * @param int $originalWidth
     * @param int $originalHeight
     * @return array<string, int>
     */
    protected function calculateOptimalDimensions(int $originalWidth, int $originalHeight): array
    {
        $maxWidth = config('smartmd.image.max_width', self::MAX_IMAGE_WIDTH);
        
        if ($originalWidth <= $maxWidth) {
            return [
                'width' => $originalWidth,
                'height' => $originalHeight
            ];
        }
        
        $scale = $maxWidth / $originalWidth;
        
        return [
            'width' => $maxWidth,
            'height' => (int) ceil($originalHeight * $scale)
        ];
    }

    /**
     * Get the upload directory path.
     *
     * @return string
     */
    protected function getUploadPath(): string
    {
        return config('smartmd.image.root', storage_path('app/public'));
    }

    /**
     * Get the public URL for an uploaded image.
     *
     * @param string $filename
     * @return string
     */
    protected function getPublicUrl(string $filename): string
    {
        $baseUrl = config('smartmd.image.url', config('app.url') . '/storage');
        return rtrim($baseUrl, '/') . '/' . $filename;
    }

    /**
     * Return a standardized error response.
     *
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function errorResponse(string $message, int $statusCode = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => true,
            'message' => $message
        ], $statusCode);
    }
}
