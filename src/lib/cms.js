/**
 * CMS API Client
 * Fetches content from Payload CMS
 */

const CMS_URL = import.meta.env.VITE_CMS_URL || 'http://localhost:3001';

/**
 * Generic fetch function with error handling
 */
async function fetchFromCMS(endpoint, options = {}) {
  const url = `${CMS_URL}/api${endpoint}`;

  try {
    const response = await fetch(url, {
      headers: {
        'Content-Type': 'application/json',
        ...options.headers,
      },
      ...options,
    });

    if (!response.ok) {
      throw new Error(`CMS API error: ${response.status} ${response.statusText}`);
    }

    return await response.json();
  } catch (error) {
    console.error(`Error fetching from CMS: ${endpoint}`, error);
    throw error;
  }
}

/**
 * Get all published blog posts
 * @param {Object} options - Query options
 * @param {number} options.limit - Number of posts to fetch
 * @param {number} options.page - Page number
 * @returns {Promise<{docs: Array, totalDocs: number, totalPages: number}>}
 */
export async function getBlogPosts({ limit = 10, page = 1 } = {}) {
  const params = new URLSearchParams({
    limit: String(limit),
    page: String(page),
    where: JSON.stringify({
      status: { equals: 'published' },
    }),
    sort: '-publishedAt',
  });

  return fetchFromCMS(`/blog-posts?${params}`);
}

/**
 * Get a single blog post by slug
 * @param {string} slug - The post slug
 * @returns {Promise<Object|null>}
 */
export async function getBlogPost(slug) {
  const params = new URLSearchParams({
    where: JSON.stringify({
      slug: { equals: slug },
      status: { equals: 'published' },
    }),
    limit: '1',
  });

  const response = await fetchFromCMS(`/blog-posts?${params}`);
  return response.docs[0] || null;
}

/**
 * Get all published use cases
 * @param {Object} options - Query options
 * @param {string} options.industry - Filter by industry
 * @returns {Promise<{docs: Array, totalDocs: number}>}
 */
export async function getUseCases({ industry = null, limit = 20 } = {}) {
  const where = { status: { equals: 'published' } };

  if (industry) {
    where.industry = { equals: industry };
  }

  const params = new URLSearchParams({
    limit: String(limit),
    where: JSON.stringify(where),
    sort: '-createdAt',
  });

  return fetchFromCMS(`/use-cases?${params}`);
}

/**
 * Get a single use case by slug
 * @param {string} slug - The use case slug
 * @returns {Promise<Object|null>}
 */
export async function getUseCase(slug) {
  const params = new URLSearchParams({
    where: JSON.stringify({
      slug: { equals: slug },
      status: { equals: 'published' },
    }),
    limit: '1',
  });

  const response = await fetchFromCMS(`/use-cases?${params}`);
  return response.docs[0] || null;
}

/**
 * Get all active team members sorted by order
 * @returns {Promise<{docs: Array}>}
 */
export async function getTeamMembers() {
  const params = new URLSearchParams({
    where: JSON.stringify({
      isActive: { equals: true },
    }),
    sort: 'order',
    limit: '50',
  });

  return fetchFromCMS(`/team-members?${params}`);
}

/**
 * Get a single team member by ID
 * @param {string} id - The team member ID
 * @returns {Promise<Object>}
 */
export async function getTeamMember(id) {
  return fetchFromCMS(`/team-members/${id}`);
}

/**
 * Get site settings (global)
 * @returns {Promise<Object>}
 */
export async function getSiteSettings() {
  return fetchFromCMS('/globals/site-settings');
}

/**
 * Get media URL from CMS
 * @param {Object} media - Media object from CMS
 * @param {string} size - Image size (thumbnail, card, hero, full)
 * @returns {string|null}
 */
export function getMediaUrl(media, size = null) {
  if (!media) return null;

  // If S3 URL is available
  if (media.url) {
    if (size && media.sizes && media.sizes[size]) {
      return media.sizes[size].url;
    }
    return media.url;
  }

  // Fallback to CMS URL
  const filename = size && media.sizes?.[size]?.filename
    ? media.sizes[size].filename
    : media.filename;

  return `${CMS_URL}/media/${filename}`;
}

/**
 * Format date in Italian locale
 * @param {string} dateString - ISO date string
 * @returns {string}
 */
export function formatDate(dateString) {
  if (!dateString) return '';

  return new Date(dateString).toLocaleDateString('it-IT', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
}

/**
 * Extract plain text from Lexical rich text content
 * @param {Object} richText - Lexical rich text object
 * @param {number} maxLength - Maximum text length
 * @returns {string}
 */
export function extractTextFromRichText(richText, maxLength = 200) {
  if (!richText || !richText.root) return '';

  function extractText(node) {
    if (node.text) return node.text;
    if (node.children) {
      return node.children.map(extractText).join(' ');
    }
    return '';
  }

  const text = extractText(richText.root).trim();

  if (maxLength && text.length > maxLength) {
    return text.substring(0, maxLength).trim() + '...';
  }

  return text;
}

/**
 * Industry labels in Italian
 */
export const industryLabels = {
  finance: 'Finanza',
  manufacturing: 'Manufacturing',
  retail: 'Retail',
  services: 'Servizi',
  logistics: 'Logistica',
  other: 'Altro',
};

/**
 * Get industry label
 * @param {string} industry - Industry code
 * @returns {string}
 */
export function getIndustryLabel(industry) {
  return industryLabels[industry] || industry;
}
