# PictureThis PHP Image Generation Setup

This PHP application now includes image generation and prompt enhancement features that match the picfe (Next.js) app functionality.

## Features

- ✅ **Image Generation**: Generate images using OpenRouter Gemini API
- ✅ **Prompt Enhancement**: Enhance user prompts using Claude AI
- ✅ **Image Upload Support**: Upload reference images for enhanced generation
- ✅ **Permission Modal**: User permission confirmation for uploaded images
- ✅ **Credit System**: Integrated with existing credit system
- ✅ **No Backend Dependency**: All API calls made directly from PHP

## Setup Instructions

### 1. Configure OpenRouter API

Edit `config/config.php` and replace the placeholder API key:

```php
putenv('OPENROUTER_API_KEY=sk-or-v1-your-actual-api-key-here');
```

Get your API key from: https://openrouter.ai/keys

### 2. Test API Connection

Run the test script to verify everything is working:

```bash
php test_openrouter.php
```

### 3. Access the Generate Page

Navigate to: `http://localhost:8000/generate`

## How It Works

### Image Generation Flow
1. User enters a prompt
2. Optional: Upload 1-2 reference images
3. Optional: Enhance prompt using AI (costs 1 credit)
4. Click "Generate Image" (costs 10 credits)
5. Image is generated using OpenRouter Gemini API
6. Credits are deducted and image is saved to database

### API Endpoints
- `POST /api/generate` - Generate image
- `POST /api/enhance` - Enhance prompt

### Database Tables
- `images` - Stores generated images
- `credit_transactions` - Tracks credit usage

## Matching picfe App Features

✅ **Prompt-only generation**
✅ **Image+prompt generation** (with reference images)
✅ **Prompt enhancement** with multiple suggestions
✅ **Permission modal** for uploaded images
✅ **Dynamic image preview**
✅ **Credit cost display**
✅ **Loading states**
✅ **Error handling**

## Troubleshooting

### API Key Issues
- Make sure your OpenRouter API key is valid and has credits
- Check that the key is properly set in `config/config.php`

### Database Issues
- Run `php setup_db.php` to ensure tables exist
- Check database connection in `config/config.php`

### Permission Issues
- Make sure the `uploads/` directory is writable
- Check file permissions for image storage

### API Rate Limits
- OpenRouter has rate limits; if you hit them, wait and try again
- Consider upgrading your OpenRouter plan for higher limits

## Environment Variables

```php
OPENROUTER_API_KEY=sk-or-v1-your-key-here
OPENROUTER_APP_URL=http://localhost:8000
OPENROUTER_GEMINI_MODEL=google/gemini-flash-1.5
OPENROUTER_MODEL=anthropic/claude-3-haiku
```

## Testing

1. Test prompt enhancement: Enter a prompt and click "Enhance Prompt"
2. Test image generation: Enter a prompt and click "Generate Image"
3. Test with images: Upload 1-2 images and generate
4. Check credits are properly deducted
5. Verify images are saved and displayed correctly

The PHP app now fully matches the picfe app's generate page functionality!
