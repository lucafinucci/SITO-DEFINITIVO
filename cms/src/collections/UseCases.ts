import type { CollectionConfig } from 'payload'

export const UseCases: CollectionConfig = {
  slug: 'use-cases',
  labels: {
    singular: 'Case Study',
    plural: 'Case Studies',
  },
  admin: {
    useAsTitle: 'title',
    defaultColumns: ['title', 'clientName', 'industry', 'status', 'updatedAt'],
    group: 'Contenuti',
    preview: (doc) => {
      if (doc?.slug) {
        return `${process.env.NEXT_PUBLIC_SITE_URL || 'http://localhost:5173'}/use-cases/${doc.slug}`
      }
      return null
    },
  },
  versions: {
    drafts: true,
  },
  access: {
    read: ({ req: { user } }) => {
      if (!user) {
        return {
          status: {
            equals: 'published',
          },
        }
      }
      return true
    },
    create: ({ req: { user } }) => !!user,
    update: ({ req: { user } }) => !!user,
    delete: ({ req: { user } }) => user?.role === 'admin',
  },
  hooks: {
    beforeChange: [
      ({ data, operation }) => {
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
      type: 'tabs',
      tabs: [
        {
          label: 'Cliente',
          fields: [
            {
              name: 'clientName',
              type: 'text',
              label: 'Nome Cliente',
              required: true,
            },
            {
              name: 'clientLogo',
              type: 'upload',
              label: 'Logo Cliente',
              relationTo: 'media',
            },
            {
              name: 'industry',
              type: 'select',
              label: 'Settore',
              required: true,
              options: [
                { label: 'Finanza', value: 'finance' },
                { label: 'Manufacturing', value: 'manufacturing' },
                { label: 'Retail', value: 'retail' },
                { label: 'Servizi', value: 'services' },
                { label: 'Logistica', value: 'logistics' },
                { label: 'Altro', value: 'other' },
              ],
            },
          ],
        },
        {
          label: 'Caso',
          fields: [
            {
              name: 'challenge',
              type: 'richText',
              label: 'La Sfida',
              required: true,
              localized: true,
              admin: {
                description: 'Descrivi il problema che il cliente stava affrontando',
              },
            },
            {
              name: 'solution',
              type: 'richText',
              label: 'La Soluzione',
              required: true,
              localized: true,
              admin: {
                description: 'Descrivi come Finch-AI ha risolto il problema',
              },
            },
          ],
        },
        {
          label: 'Risultati',
          fields: [
            {
              name: 'results',
              type: 'array',
              label: 'Metriche di Risultato',
              labels: {
                singular: 'Metrica',
                plural: 'Metriche',
              },
              minRows: 1,
              maxRows: 6,
              fields: [
                {
                  name: 'metric',
                  type: 'text',
                  label: 'Metrica',
                  required: true,
                  admin: {
                    placeholder: 'es. Riduzione tempi operativi',
                  },
                },
                {
                  name: 'value',
                  type: 'text',
                  label: 'Valore',
                  required: true,
                  admin: {
                    placeholder: 'es. -70%',
                  },
                },
              ],
            },
          ],
        },
        {
          label: 'Testimonianza',
          fields: [
            {
              name: 'testimonialQuote',
              type: 'textarea',
              label: 'Citazione',
              localized: true,
              admin: {
                description: 'Testimonianza del cliente (opzionale)',
              },
            },
            {
              name: 'testimonialAuthor',
              type: 'text',
              label: 'Autore Testimonianza',
              admin: {
                placeholder: 'es. Mario Rossi, CEO',
              },
            },
          ],
        },
        {
          label: 'Media',
          fields: [
            {
              name: 'featuredImage',
              type: 'upload',
              label: 'Immagine Principale',
              relationTo: 'media',
              required: true,
            },
            {
              name: 'images',
              type: 'array',
              label: 'Galleria Immagini',
              labels: {
                singular: 'Immagine',
                plural: 'Immagini',
              },
              fields: [
                {
                  name: 'image',
                  type: 'upload',
                  label: 'Immagine',
                  relationTo: 'media',
                  required: true,
                },
                {
                  name: 'caption',
                  type: 'text',
                  label: 'Didascalia',
                },
              ],
            },
          ],
        },
      ],
    },
    {
      name: 'status',
      type: 'select',
      label: 'Stato',
      required: true,
      defaultValue: 'draft',
      options: [
        { label: 'Bozza', value: 'draft' },
        { label: 'Pubblicato', value: 'published' },
      ],
      admin: {
        position: 'sidebar',
      },
    },
  ],
}
