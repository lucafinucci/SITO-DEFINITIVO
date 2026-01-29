import type { CollectionConfig } from 'payload'

export const BlogPosts: CollectionConfig = {
  slug: 'blog-posts',
  labels: {
    singular: 'Articolo Blog',
    plural: 'Articoli Blog',
  },
  admin: {
    useAsTitle: 'title',
    defaultColumns: ['title', 'status', 'author', 'publishedAt', 'updatedAt'],
    group: 'Contenuti',
    preview: (doc) => {
      if (doc?.slug) {
        return `${process.env.NEXT_PUBLIC_SITE_URL || 'http://localhost:5173'}/blog/${doc.slug}`
      }
      return null
    },
  },
  versions: {
    drafts: true,
  },
  access: {
    read: ({ req: { user } }) => {
      // Public can only read published posts
      if (!user) {
        return {
          status: {
            equals: 'published',
          },
        }
      }
      // Logged in users can read all
      return true
    },
    create: ({ req: { user } }) => !!user,
    update: ({ req: { user } }) => !!user,
    delete: ({ req: { user } }) => user?.role === 'admin',
  },
  hooks: {
    beforeChange: [
      ({ data, operation }) => {
        // Auto-generate slug from title if not set
        if (operation === 'create' && data.title && !data.slug) {
          data.slug = data.title
            .toLowerCase()
            .replace(/[àáâãäå]/g, 'a')
            .replace(/[èéêë]/g, 'e')
            .replace(/[ìíîï]/g, 'i')
            .replace(/[òóôõö]/g, 'o')
            .replace(/[ùúûü]/g, 'u')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-|-$/g, '')
        }
        return data
      },
    ],
  },
  fields: [
    {
      name: 'title',
      type: 'text',
      label: 'Titolo',
      required: true,
      localized: true,
    },
    {
      name: 'slug',
      type: 'text',
      label: 'Slug (URL)',
      required: true,
      unique: true,
      admin: {
        position: 'sidebar',
        description: 'Generato automaticamente dal titolo se lasciato vuoto',
      },
      hooks: {
        beforeValidate: [
          ({ value, data }) => {
            if (!value && data?.title) {
              return data.title
                .toLowerCase()
                .replace(/[àáâãäå]/g, 'a')
                .replace(/[èéêë]/g, 'e')
                .replace(/[ìíîï]/g, 'i')
                .replace(/[òóôõö]/g, 'o')
                .replace(/[ùúûü]/g, 'u')
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-|-$/g, '')
            }
            return value
          },
        ],
      },
    },
    {
      name: 'excerpt',
      type: 'textarea',
      label: 'Estratto',
      required: true,
      localized: true,
      admin: {
        description: 'Breve descrizione per le anteprime (max 200 caratteri)',
      },
      maxLength: 200,
    },
    {
      name: 'content',
      type: 'richText',
      label: 'Contenuto',
      required: true,
      localized: true,
    },
    {
      name: 'featuredImage',
      type: 'upload',
      label: 'Immagine in Evidenza',
      relationTo: 'media',
      required: true,
    },
    {
      name: 'tags',
      type: 'array',
      label: 'Tag',
      labels: {
        singular: 'Tag',
        plural: 'Tag',
      },
      fields: [
        {
          name: 'tag',
          type: 'text',
          label: 'Tag',
          required: true,
        },
      ],
    },
    {
      name: 'author',
      type: 'relationship',
      label: 'Autore',
      relationTo: 'users',
      required: true,
      admin: {
        position: 'sidebar',
      },
    },
    {
      name: 'status',
      type: 'select',
      label: 'Stato',
      required: true,
      defaultValue: 'draft',
      options: [
        {
          label: 'Bozza',
          value: 'draft',
        },
        {
          label: 'Pubblicato',
          value: 'published',
        },
      ],
      admin: {
        position: 'sidebar',
      },
    },
    {
      name: 'publishedAt',
      type: 'date',
      label: 'Data Pubblicazione',
      admin: {
        position: 'sidebar',
        date: {
          pickerAppearance: 'dayAndTime',
        },
      },
    },
    {
      name: 'seo',
      type: 'group',
      label: 'SEO',
      fields: [
        {
          name: 'metaTitle',
          type: 'text',
          label: 'Meta Titolo',
          admin: {
            description: 'Lascia vuoto per usare il titolo dell\'articolo',
          },
        },
        {
          name: 'metaDescription',
          type: 'textarea',
          label: 'Meta Descrizione',
          admin: {
            description: 'Lascia vuoto per usare l\'estratto',
          },
        },
        {
          name: 'ogImage',
          type: 'upload',
          label: 'Immagine Social (OG Image)',
          relationTo: 'media',
          admin: {
            description: 'Lascia vuoto per usare l\'immagine in evidenza',
          },
        },
      ],
    },
  ],
}
