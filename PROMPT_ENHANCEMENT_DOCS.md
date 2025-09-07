# Prompt Enhancement Feature Documentation

## Overview
The Prompt Enhancement feature uses LLM technology to generate detailed, professional prompts for AI image generation. It transforms simple user input into comprehensive, artistic descriptions that produce better image results.

## Features
- **LLM Integration**: Uses OpenRouter API with Anthropic Claude-3-Haiku for intelligent prompt enhancement
- **Fallback System**: Provides predefined enhanced prompts when API is unavailable
- **Clean UI**: Non-intrusive interface that doesn't clutter the generation studio
- **Expandable View**: Shows 3 prompts by default, with option to view all 10
- **One-Click Selection**: Users can click any enhanced prompt to use it immediately

## Technical Implementation

### Backend (`/api/prompts/enhance`)
- **Route**: `POST /api/prompts/enhance`
- **Authentication**: Required (supports demo token for testing)
- **Input**: `{ "prompt": "user input text" }`
- **Output**: `{ "data": { "originalPrompt": "...", "enhancedPrompts": [...] } }`

### Frontend Integration
- **Location**: Image Generation Studio, integrated with prompt input
- **Activation**: "🚀 Enhance Prompt" button next to character counter
- **Display**: Collapsible panel showing enhanced options
- **Selection**: Click-to-use functionality

### API Configuration
```env
OPENROUTER_API_KEY=your_openrouter_api_key_here
SITE_URL=http://localhost:3011
```

## User Experience Flow
1. User enters basic prompt (e.g., "sunset mountain")
2. Clicks "🚀 Enhance Prompt" button
3. System generates 10 detailed prompts via LLM
4. Shows first 3 prompts with "View All 10" option
5. User clicks preferred prompt to use it
6. Enhanced prompt populates the generation input
7. User proceeds with image generation

## Example Enhancement
**Input**: "sunset mountain"

**Enhanced Output**:
- "Sunset mountain, highly detailed, photorealistic, professional photography, dramatic lighting, sharp focus"
- "Sunset mountain, digital art style, vibrant colors, concept art, artstation trending, detailed textures"
- "Sunset mountain, cinematic composition, golden hour lighting, depth of field, atmospheric perspective"
- ... (7 more variations)

## Fallback Prompts
When the OpenRouter API is unavailable, the system provides 10 predefined enhancement patterns covering various artistic styles:
- Photorealistic photography
- Digital concept art
- Cinematic composition
- Oil painting style
- Modern minimalist
- Fantasy art
- Vintage photography
- Hyperrealistic 3D
- Watercolor painting
- Cyberpunk aesthetic

## Development Notes
- Graceful error handling with fallback prompts
- Demo token support for testing
- Configurable LLM model (currently Claude-3-Haiku)
- Responsive UI that adapts to content
- No external dependencies beyond existing stack
