import type { CollectionConfig } from 'payload'

export const TeamMembers: CollectionConfig = {
  slug: 'team-members',
  labels: {
    singular: 'Membro Team',
    plural: 'Team',
  },
  admin: {
    useAsTitle: 'name',
    defaultColumns: ['name', 'role', 'order', 'updatedAt'],
    group: 'Contenuti',
  },
  access: {
    read: () => true, // Public access
    create: ({ req: { user } }) => !!user,
    update: ({ req: { user } }) => !!user,
    delete: ({ req: { user } }) => user?.role === 'admin',
  },
  fields: [
    {
      name: 'name',
      type: 'text',
      label: 'Nome Completo',
      required: true,
    },
    {
      name: 'role',
      type: 'text',
      label: 'Ruolo',
      required: true,
      admin: {
        placeholder: 'es. CEO & Co-Founder',
      },
    },
    {
      name: 'photo',
      type: 'upload',
      label: 'Foto',
      relationTo: 'media',
      required: true,
    },
    {
      name: 'bio',
      type: 'richText',
      label: 'Biografia',
      required: true,
      localized: true,
    },
    {
      name: 'shortBio',
      type: 'textarea',
      label: 'Bio Breve',
      admin: {
        description: 'Una o due frasi per le card (max 150 caratteri)',
      },
      maxLength: 150,
      localized: true,
    },
    {
      name: 'linkedin',
      type: 'text',
      label: 'LinkedIn',
      admin: {
        placeholder: 'https://linkedin.com/in/username',
      },
      validate: (value) => {
        if (value && !value.includes('linkedin.com')) {
          return 'Inserisci un URL LinkedIn valido'
        }
        return true
      },
    },
    {
      name: 'email',
      type: 'email',
      label: 'Email',
      admin: {
        description: 'Email pubblica (opzionale)',
      },
    },
    {
      name: 'order',
      type: 'number',
      label: 'Ordine',
      required: true,
      defaultValue: 0,
      admin: {
        position: 'sidebar',
        description: 'Numeri più bassi appaiono prima',
      },
    },
    {
      name: 'isActive',
      type: 'checkbox',
      label: 'Attivo',
      defaultValue: true,
      admin: {
        position: 'sidebar',
        description: 'Deseleziona per nascondere dal sito',
      },
    },
  ],
}
