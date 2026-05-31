import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';

// Italian (default) namespaces
import itCommon from './locales/it/common.json';
import itHome from './locales/it/home.json';
import itBlog from './locales/it/blog.json';
import itArticles from './locales/it/articles.json';
import itLegal from './locales/it/legal.json';
// Italian solutions (one file per page, merged into the `solutions` namespace)
import itSolFinance from './locales/it/solutions/finance.json';
import itSolDocument from './locales/it/solutions/document.json';
import itSolWarehouse from './locales/it/solutions/warehouse.json';
import itSolSynapse from './locales/it/solutions/synapse.json';
import itSolAps from './locales/it/solutions/aps.json';

// English namespaces
import enCommon from './locales/en/common.json';
import enHome from './locales/en/home.json';
import enBlog from './locales/en/blog.json';
import enArticles from './locales/en/articles.json';
import enLegal from './locales/en/legal.json';
import enSolFinance from './locales/en/solutions/finance.json';
import enSolDocument from './locales/en/solutions/document.json';
import enSolWarehouse from './locales/en/solutions/warehouse.json';
import enSolSynapse from './locales/en/solutions/synapse.json';
import enSolAps from './locales/en/solutions/aps.json';

const itSolutions = {
  finance: itSolFinance,
  document: itSolDocument,
  warehouse: itSolWarehouse,
  synapse: itSolSynapse,
  aps: itSolAps,
};
const enSolutions = {
  finance: enSolFinance,
  document: enSolDocument,
  warehouse: enSolWarehouse,
  synapse: enSolSynapse,
  aps: enSolAps,
};

export const SUPPORTED_LANGS = ['it', 'en'];
export const DEFAULT_LANG = 'it';

const resources = {
  it: {
    common: itCommon,
    home: itHome,
    solutions: itSolutions,
    blog: itBlog,
    articles: itArticles,
    legal: itLegal,
  },
  en: {
    common: enCommon,
    home: enHome,
    solutions: enSolutions,
    blog: enBlog,
    articles: enArticles,
    legal: enLegal,
  },
};

i18n.use(initReactI18next).init({
  resources,
  lng: DEFAULT_LANG,
  fallbackLng: DEFAULT_LANG,
  defaultNS: 'common',
  ns: ['common', 'home', 'solutions', 'blog', 'articles', 'legal'],
  interpolation: { escapeValue: false },
  react: { useSuspense: false },
});

export default i18n;
