import { buildConfig } from 'payload'
import { postgresAdapter } from '@payloadcms/db-postgres'
import { lexicalEditor } from '@payloadcms/richtext-lexical'
import { s3Storage } from '@payloadcms/storage-s3'
import sharp from 'sharp'
import path from 'path'
import { fileURLToPath } from 'url'

// Collections
import { Users } from './collections/Users'
import { Media } from './collections/Media'
import { BlogPosts } from './collections/BlogPosts'
import { UseCases } from './collections/UseCases'
import { TeamMembers } from './collections/TeamMembers'

// Globals
import { SiteSettings } from './globals/SiteSettings'

const filename = fileURLToPath(import.meta.url)
const dirname = path.dirname(filename)

// Check if S3 is configured (for production)
const useS3Storage = !!(
  process.env.S3_ENDPOINT &&
  process.env.S3_BUCKET &&
  process.env.S3_ACCESS_KEY &&
  process.env.S3_SECRET_KEY
)

// Build plugins array conditionally
const plugins = []

if (useS3Storage) {
  console.log('📦 Using S3 storage for media files')
  plugins.push(
    s3Storage({
      collections: {
        media: {
          prefix: 'media',
        },
      },
      bucket: process.env.S3_BUCKET!,
      config: {
        credentials: {
          accessKeyId: process.env.S3_ACCESS_KEY!,
          secretAccessKey: process.env.S3_SECRET_KEY!,
        },
        region: process.env.S3_REGION || 'fsn1',
        endpoint: process.env.S3_ENDPOINT,
        forcePathStyle: true, // Required for S3-compatible storage like Hetzner
      },
    })
  )
} else {
  console.log('📁 Using local storage for media files (development mode)')
}

export default buildConfig({
  admin: {
    user: Users.slug,
    meta: {
      titleSuffix: '- Finch-AI CMS',
      favicon: '/favicon.ico',
      ogImage: '/og-image.png',
    },
    dateFormat: 'dd/MM/yyyy HH:mm',
  },

  // Italian localization for admin UI
  i18n: {
    fallbackLanguage: 'it',
  },
  localization: {
    locales: [
      {
        label: 'Italiano',
        code: 'it',
      },
      {
        label: 'English',
        code: 'en',
      },
    ],
    defaultLocale: 'it',
    fallback: true,
  },

  collections: [Users, Media, BlogPosts, UseCases, TeamMembers],
  globals: [SiteSettings],

  editor: lexicalEditor(),

  secret: process.env.PAYLOAD_SECRET || 'your-secret-key-change-in-production',

  typescript: {
    outputFile: path.resolve(dirname, 'payload-types.ts'),
  },

  db: postgresAdapter({
    pool: {
      connectionString: process.env.DATABASE_URI || 'postgresql://payload:payload@localhost:5432/payload',
    },
  }),

  sharp,

  plugins,

  // CORS settings for frontend
  cors: [
    'http://localhost:5173',
    'http://127.0.0.1:5173',
    'https://finch-ai.it',
    'https://www.finch-ai.it',
  ],

  // Rate limiting
  rateLimit: {
    max: 500,
    window: 60000,
  },

  // Upload settings
  upload: {
    limits: {
      fileSize: 10000000, // 10MB
    },
  },
})
