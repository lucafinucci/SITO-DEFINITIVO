import type { CollectionConfig } from 'payload'

export const Users: CollectionConfig = {
  slug: 'users',
  labels: {
    singular: 'Utente',
    plural: 'Utenti',
  },
  admin: {
    useAsTitle: 'email',
    defaultColumns: ['email', 'name', 'role', 'createdAt'],
    group: 'Amministrazione',
  },
  auth: true,
  access: {
    // Admins can do everything
    create: ({ req: { user } }) => user?.role === 'admin',
    delete: ({ req: { user } }) => user?.role === 'admin',
    // Users can read themselves, admins can read all
    read: ({ req: { user } }) => {
      if (user?.role === 'admin') return true
      return {
        id: {
          equals: user?.id,
        },
      }
    },
    // Users can update themselves, admins can update all
    update: ({ req: { user } }) => {
      if (user?.role === 'admin') return true
      return {
        id: {
          equals: user?.id,
        },
      }
    },
  },
  fields: [
    {
      name: 'name',
      type: 'text',
      label: 'Nome',
      required: true,
    },
    {
      name: 'role',
      type: 'select',
      label: 'Ruolo',
      required: true,
      defaultValue: 'editor',
      options: [
        {
          label: 'Amministratore',
          value: 'admin',
        },
        {
          label: 'Editor',
          value: 'editor',
        },
      ],
      access: {
        // Only admins can change roles
        update: ({ req: { user } }) => user?.role === 'admin',
      },
    },
    {
      name: 'avatar',
      type: 'upload',
      label: 'Avatar',
      relationTo: 'media',
    },
  ],
  timestamps: true,
}
