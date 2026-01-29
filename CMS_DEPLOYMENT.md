# Payload CMS Deployment Guide

## Overview

This project now includes **Payload CMS v3** for content management, running alongside the existing React + Vite website and PHP Area Clienti.

**Storage Modes:**
- **Local Development**: Media files stored in `cms/media/` folder
- **Production (Hetzner)**: Media files stored in Hetzner Object Storage (S3-compatible)

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                         NGINX                                │
│                    (Reverse Proxy)                           │
├──────────┬──────────┬──────────────────┬───────────────────┤
│   /      │  /admin  │      /api        │   /area-clienti   │
│          │  /media  │                  │                   │
│  Website │   CMS    │    CMS API       │   PHP Backend     │
│  (React) │ (Payload)│   (REST/GraphQL) │   (MySQL)         │
├──────────┴──────────┴──────────────────┴───────────────────┤
│                       PostgreSQL                            │
│                    (CMS Database)                           │
└─────────────────────────────────────────────────────────────┘
```

---

## Local Development (Quick Start)

### 1. Start PostgreSQL

```bash
# Start PostgreSQL container
docker run -d \
  --name finch-postgres \
  -e POSTGRES_USER=payload \
  -e POSTGRES_PASSWORD=payload \
  -e POSTGRES_DB=payload \
  -p 5432:5432 \
  postgres:16-alpine
```

### 2. Setup & Run CMS

```bash
# Navigate to CMS directory
cd cms

# Copy environment file
cp .env.example .env
# No need to edit - defaults work for local dev!

# Install dependencies
npm install

# Start development server
npm run dev
```

**CMS Admin**: http://localhost:3001/admin

> Media files will be stored locally in `cms/media/` folder.

### 3. Start Website (separate terminal)

```bash
# In project root
npm install
npm run dev
```

**Website**: http://localhost:5173

### 4. Create First Admin User

1. Open http://localhost:3001/admin
2. Create your first admin account
3. Login and start adding content

---

## Environment Variables

### Local Development (`cms/.env`)

```env
# Database
DATABASE_URI=postgresql://payload:payload@localhost:5432/payload

# Secret (default is fine for local dev)
PAYLOAD_SECRET=dev-secret-key-for-local-testing-only-32chars

# URLs
NEXT_PUBLIC_SITE_URL=http://localhost:5173
NEXT_PUBLIC_SERVER_URL=http://localhost:3001

# S3 - Leave commented out for local storage
# S3_ENDPOINT=
# S3_BUCKET=
# S3_ACCESS_KEY=
# S3_SECRET_KEY=
```

### Production (`cms/.env`)

```env
# Database
DATABASE_URI=postgresql://payload:STRONG_PASSWORD@postgres:5432/payload

# Secret (CHANGE THIS!)
PAYLOAD_SECRET=your-production-secret-minimum-32-characters

# URLs
NEXT_PUBLIC_SITE_URL=https://finch-ai.it
NEXT_PUBLIC_SERVER_URL=https://finch-ai.it

# Hetzner Object Storage
S3_ENDPOINT=https://fsn1.your-objectstorage.com
S3_BUCKET=finch-ai-media
S3_ACCESS_KEY=your-access-key
S3_SECRET_KEY=your-secret-key
S3_REGION=fsn1
```

---

## Using Docker Compose (Alternative)

For a complete local setup using Docker:

```bash
# Start all services (website, CMS, PostgreSQL)
docker-compose up -d

# Check logs
docker-compose logs -f

# Stop
docker-compose down
```

This will:
- Start PostgreSQL on port 5432
- Start CMS on port 3001 (with local media storage)
- Start Website on port 5173

---

## Production Deployment (Hetzner)

### 1. Setup Hetzner Object Storage

1. Create a bucket in Hetzner Cloud Console
2. Generate access credentials
3. Note the endpoint URL (e.g., `https://fsn1.your-objectstorage.com`)

### 2. Setup Server

```bash
# SSH into your Hetzner server
ssh user@your-server

# Install Docker & Docker Compose
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER

# Clone repository
git clone your-repo
cd your-repo
```

### 3. Configure Environment

```bash
# Create production .env
cat > .env << 'EOF'
POSTGRES_PASSWORD=your-strong-db-password
PAYLOAD_SECRET=your-production-secret-minimum-32-characters
S3_ENDPOINT=https://fsn1.your-objectstorage.com
S3_BUCKET=finch-ai-media
S3_ACCESS_KEY=your-access-key
S3_SECRET_KEY=your-secret-key
S3_REGION=fsn1
SITE_URL=https://finch-ai.it
CMS_URL=https://finch-ai.it
EOF
```

### 4. Setup SSL Certificates

```bash
mkdir -p certs

# Option A: Let's Encrypt (recommended)
certbot certonly --standalone -d finch-ai.it -d www.finch-ai.it
cp /etc/letsencrypt/live/finch-ai.it/fullchain.pem certs/
cp /etc/letsencrypt/live/finch-ai.it/privkey.pem certs/
```

### 5. Deploy

```bash
# Build and start with production profile
docker-compose --profile production up -d --build

# Check status
docker-compose ps

# View logs
docker-compose logs -f cms
```

---

## Content Types

### Blog Posts (`/blog`)
- Title, slug, excerpt, rich text content
- Featured image, tags, author
- Draft/Published status
- SEO metadata

### Use Cases (`/use-cases`)
- Client info (name, logo, industry)
- Challenge and Solution (rich text)
- Results metrics (metric + value pairs)
- Testimonial quote
- Image gallery

### Team Members (`/team`)
- Name, role, photo, bio
- LinkedIn, email
- Order for sorting
- Active/inactive toggle

### Site Settings (Global)
- Site name, logo, favicon
- Contact info (email, phones, address)
- Social media links
- Footer content

---

## User Roles

| Role | Permissions |
|------|-------------|
| **Admin** | Full access: users, settings, all content |
| **Editor** | Create/edit content, no user management |

---

## Backup Strategy

### Database Backup

```bash
# Backup PostgreSQL
docker-compose exec postgres pg_dump -U payload payload > backup_$(date +%Y%m%d).sql

# Restore
docker-compose exec -T postgres psql -U payload payload < backup.sql
```

### Media Backup (Local Development)

```bash
# Backup media folder
tar -czvf media_backup_$(date +%Y%m%d).tar.gz cms/media/
```

### Media Backup (Production with S3)

Configure in Hetzner Cloud Console:
- Enable versioning
- Set lifecycle rules (archive old versions after 30 days)

---

## Migrating Data from Local to Production

### 1. Export Local Database

```bash
docker exec finch-postgres pg_dump -U payload payload > local_backup.sql
```

### 2. Upload Media to S3

```bash
# Install rclone
brew install rclone  # or apt install rclone

# Configure rclone for Hetzner S3
rclone config
# Add new remote, type: s3, provider: Other

# Sync media folder to S3
rclone sync cms/media/ hetzner:finch-ai-media/media/
```

### 3. Import Database on Production

```bash
scp local_backup.sql user@server:~/
ssh user@server
docker-compose exec -T postgres psql -U payload payload < ~/local_backup.sql
```

---

## Troubleshooting

### CMS won't start
```bash
# Check logs
docker-compose logs cms

# Common issues:
# - DATABASE_URI incorrect
# - PAYLOAD_SECRET too short (<32 chars)
# - Port 3001 already in use
```

### Images not loading (Local)
```bash
# Check media folder permissions
ls -la cms/media/

# Ensure the folder exists
mkdir -p cms/media
```

### Images not loading (Production)
```bash
# Check S3 configuration
# Verify bucket permissions (public read for media)
# Check S3_ENDPOINT format
```

### API returns 404
```bash
# Ensure CMS is running
curl http://localhost:3001/api/blog-posts

# Check CORS settings in payload.config.ts
```

---

## Support

- Payload CMS Docs: https://payloadcms.com/docs
- GitHub Issues: [your-repo]/issues
