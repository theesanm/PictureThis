# Image Retention System

This system implements automatic soft deletion of old images to manage storage space while preserving transaction records.

## Configuration

Configure the retention settings in your `.htaccess` file:

```apache
# Image Retention Configuration
# Number of days to keep images before marking as deleted
SetEnv IMAGE_RETENTION_DAYS "7"
# Minimum number of images to keep per user (to avoid empty galleries)
SetEnv MIN_IMAGES_PER_USER "3"
```

## How It Works

1. **Soft Deletion**: Images older than the retention period are marked as "deleted" in the database using `has_usage_permission = -1`
2. **Preservation**: Transaction records and image metadata are preserved
3. **Visibility**: Soft deleted images don't appear in galleries, dashboard, or recent images
4. **Minimum Count**: Each user keeps at least the minimum number of images to avoid empty galleries

## Maintenance Scripts

### 1. Mark Old Images as Deleted

Run this script periodically to mark old images as soft deleted:

```bash
php maintain_image_retention.php
```

**What it does:**
- Identifies images older than `IMAGE_RETENTION_DAYS`
- Respects `MIN_IMAGES_PER_USER` setting
- Marks old images with `has_usage_permission = -1`
- Preserves transaction records

### 2. Clean Up Physical Files

Run this script to physically delete the soft deleted image files:

```bash
# Dry run (see what would be deleted)
php cleanup_image_files.php --dry-run

# Actually delete the files
php cleanup_image_files.php
```

**What it does:**
- Finds all images with `has_usage_permission = -1`
- Physically deletes the image files from disk
- Preserves database records for transaction history

## Database Changes

**No schema changes required!** The system uses the existing `has_usage_permission` field:
- `NULL` or any value except `-1`: Active image
- `-1`: Soft deleted image

## Automated Usage

Set up a cron job to run the maintenance script periodically:

```bash
# Run daily at 2 AM
0 2 * * * cd /path/to/your/app && php maintain_image_retention.php >> /var/log/image_maintenance.log 2>&1
```

## Monitoring

Check the soft deleted images:

```sql
-- Count soft deleted images
SELECT COUNT(*) FROM images WHERE has_usage_permission = -1;

-- View soft deleted images by user
SELECT user_id, COUNT(*) as deleted_count
FROM images
WHERE has_usage_permission = -1
GROUP BY user_id
ORDER BY deleted_count DESC;
```

## Benefits

- ✅ **Storage Management**: Automatically manages disk space
- ✅ **User Experience**: Maintains minimum image count per user
- ✅ **Transaction Integrity**: Preserves all transaction records
- ✅ **Flexible**: Easy to adjust retention periods
- ✅ **Safe**: Dry-run mode for testing file deletion

## Files Modified

- `.htaccess` - Added retention configuration
- `src/controllers/GenerateController.php` - Added soft delete logic
- `src/controllers/GalleryController.php` - Updated to exclude soft deleted images
- `src/controllers/DashboardController.php` - Updated to exclude soft deleted images
- `src/controllers/AdminController.php` - Updated image count queries
- `src/views/home.php` - Updated total image count
- `maintain_image_retention.php` - Maintenance script
- `cleanup_image_files.php` - File cleanup script