# Storage Setup Instructions

## Important: Storage Link Configuration

After deploying the application or cloning the repository, you MUST create a symbolic link for storage to make uploaded/generated files accessible via web.

### Method 1: Using Artisan Command (Recommended)

```bash
php artisan storage:link
```

### Method 2: Manual Symlink Creation

If you don't have access to artisan command:

```bash
ln -s ../storage/app/public public/storage
```

### Method 3: In Docker Container

If running in Docker:

```bash
docker exec -it ai-generator-app php artisan storage:link
```

## Verify Installation

After creating the symlink, verify it exists:

```bash
ls -la public/storage
```

You should see output like:
```
lrwxrwxrwx  1 user user   21 Oct 23 16:33 storage -> ../storage/app/public
```

## Directory Permissions

Ensure the storage directories have proper permissions:

```bash
chmod -R 775 storage/app/public
mkdir -p storage/app/public/infographics
chmod -R 775 storage/app/public/infographics
```

## Troubleshooting

### Images not displaying?

1. Check if the symlink exists: `ls -la public/storage`
2. Check directory permissions: `ls -la storage/app/public`
3. Check if infographics directory exists: `ls -la storage/app/public/infographics`
4. Check web server configuration (Nginx/Apache) allows serving static files

### Storage link already exists error?

If you get "The [public/storage] link already exists" error:

```bash
rm public/storage
php artisan storage:link
```
