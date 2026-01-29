import type { GlobalConfig } from 'payload'

export const SiteSettings: GlobalConfig = {
  slug: 'site-settings',
  label: 'Impostazioni Sito',
  admin: {
    group: 'Amministrazione',
  },
  access: {
    read: () => true, // Public access
    update: ({ req: { user } }) => user?.role === 'admin',
  },
  fields: [
    {
      type: 'tabs',
      tabs: [
        {
          label: 'Generale',
          fields: [
            {
              name: 'siteName',
              type: 'text',
              label: 'Nome Sito',
              required: true,
              defaultValue: 'Finch-AI',
            },
            {
              name: 'siteDescription',
              type: 'textarea',
              label: 'Descrizione Sito',
              localized: true,
              admin: {
                description: 'Descrizione breve per SEO e meta tag',
              },
            },
            {
              name: 'logo',
              type: 'upload',
              label: 'Logo',
              relationTo: 'media',
            },
            {
              name: 'favicon',
              type: 'upload',
              label: 'Favicon',
              relationTo: 'media',
            },
          ],
        },
        {
          label: 'Contatti',
          fields: [
            {
              name: 'contactEmail',
              type: 'email',
              label: 'Email Principale',
              defaultValue: 'info@finch-ai.it',
            },
            {
              name: 'phones',
              type: 'array',
              label: 'Numeri di Telefono',
              labels: {
                singular: 'Telefono',
                plural: 'Telefoni',
              },
              fields: [
                {
                  name: 'number',
                  type: 'text',
                  label: 'Numero',
                  required: true,
                },
                {
                  name: 'label',
                  type: 'text',
                  label: 'Etichetta',
                  admin: {
                    placeholder: 'es. Ufficio, Mobile, WhatsApp',
                  },
                },
                {
                  name: 'isWhatsapp',
                  type: 'checkbox',
                  label: 'È WhatsApp',
                  defaultValue: false,
                },
              ],
            },
            {
              name: 'address',
              type: 'group',
              label: 'Indirizzo',
              fields: [
                {
                  name: 'street',
                  type: 'text',
                  label: 'Via',
                },
                {
                  name: 'city',
                  type: 'text',
                  label: 'Città',
                },
                {
                  name: 'postalCode',
                  type: 'text',
                  label: 'CAP',
                },
                {
                  name: 'province',
                  type: 'text',
                  label: 'Provincia',
                },
                {
                  name: 'country',
                  type: 'text',
                  label: 'Paese',
                  defaultValue: 'Italia',
                },
              ],
            },
            {
              name: 'businessHours',
              type: 'text',
              label: 'Orari di Lavoro',
              defaultValue: 'Lun-Ven 9:00-18:00',
            },
          ],
        },
        {
          label: 'Social',
          fields: [
            {
              name: 'socialLinks',
              type: 'group',
              label: 'Link Social',
              fields: [
                {
                  name: 'linkedin',
                  type: 'text',
                  label: 'LinkedIn',
                  admin: {
                    placeholder: 'https://linkedin.com/company/finch-ai',
                  },
                },
                {
                  name: 'twitter',
                  type: 'text',
                  label: 'Twitter/X',
                },
                {
                  name: 'github',
                  type: 'text',
                  label: 'GitHub',
                },
                {
                  name: 'instagram',
                  type: 'text',
                  label: 'Instagram',
                },
                {
                  name: 'facebook',
                  type: 'text',
                  label: 'Facebook',
                },
              ],
            },
          ],
        },
        {
          label: 'Footer',
          fields: [
            {
              name: 'footerText',
              type: 'richText',
              label: 'Testo Footer',
              localized: true,
            },
            {
              name: 'copyrightText',
              type: 'text',
              label: 'Testo Copyright',
              defaultValue: 'Finch-AI S.r.l. Tutti i diritti riservati.',
            },
            {
              name: 'legalLinks',
              type: 'array',
              label: 'Link Legali',
              labels: {
                singular: 'Link',
                plural: 'Link',
              },
              fields: [
                {
                  name: 'label',
                  type: 'text',
                  label: 'Etichetta',
                  required: true,
                },
                {
                  name: 'url',
                  type: 'text',
                  label: 'URL',
                  required: true,
                },
              ],
            },
          ],
        },
        {
          label: 'SEO',
          fields: [
            {
              name: 'defaultMetaTitle',
              type: 'text',
              label: 'Meta Title Default',
              admin: {
                description: 'Titolo usato quando una pagina non ne specifica uno',
              },
            },
            {
              name: 'defaultMetaDescription',
              type: 'textarea',
              label: 'Meta Description Default',
            },
            {
              name: 'defaultOgImage',
              type: 'upload',
              label: 'OG Image Default',
              relationTo: 'media',
            },
          ],
        },
      ],
    },
  ],
}
