import React, { useEffect, useRef } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import SEO from '@/components/SEO';
import Navbar from '@/components/Navbar';
import Footer from '@/components/Footer';
import { useContactModal } from '@/context/ContactModalContext';
import { useLocale, useLocalizedPath } from '@/i18n/routing';
import '@/styles/synapse.css';
import '@/styles/solutions.css';
import {
    ArrowRight,
    ArrowDown,
    Upload,
    Brain,
    MessageSquare,
    FileText,
    BarChart3,
    Globe,
    Lock,
    Trash2,
    Mail,
    Server,
    ShieldCheck,
    Scale,
    PlayCircle,
    Play,
    LineChart,
    FileBarChart2,
    CheckCircle,
    Check,
    Minus,
} from 'lucide-react';

const YOUTUBE_URL = 'https://www.youtube.com/@Finch-AI';
const APP_URL = 'https://bi.finch-ai.it';

// Icons paired by index with the translated data arrays.
const WF_ICONS = [Upload, Brain, FileBarChart2, MessageSquare];
const STEP_ICONS = [Upload, Brain, MessageSquare];
const FEAT_ICONS = [FileBarChart2, BarChart3, MessageSquare, LineChart, FileText, Globe];
const PRIV_ICONS = [Upload, Trash2, Mail, Server];

function useReveal() {
    useEffect(() => {
        const els = document.querySelectorAll('.reveal');
        const io = new IntersectionObserver(
            (entries) => entries.forEach((e) => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } }),
            { threshold: 0.1 }
        );
        els.forEach((el) => io.observe(el));
        return () => io.disconnect();
    }, []);
}

const renderVal = (v) => {
    if (v === true) return <Check />;
    if (v === false || v === null) return <Minus className="mut" />;
    return v;
};

export default function FinanceIntelligence() {
    useReveal();
    const { t } = useTranslation('solutions');
    const locale = useLocale();
    const lp = useLocalizedPath();
    const { openContact } = useContactModal();
    const videoRef = useRef(null);

    useEffect(() => { window.scrollTo(0, 0); }, []);

    const workflow = t('finance.workflow', { returnObjects: true });
    const steps = t('finance.steps', { returnObjects: true });
    const features = t('finance.features', { returnObjects: true });
    const privacy = t('finance.privacy', { returnObjects: true });
    const plans = t('finance.plans', { returnObjects: true });
    const rows = t('finance.rows', { returnObjects: true });
    const faqs = t('finance.faqs', { returnObjects: true });
    const customPrice = t('finance.customPrice');

    const financeJsonLd = [
        {
            "@context": "https://schema.org",
            "@type": "SoftwareApplication",
            "name": "Finch-AI Finance Intelligence",
            "applicationCategory": "BusinessApplication",
            "applicationSubCategory": "FinancialApplication",
            "operatingSystem": "Web",
            "url": "https://finch-ai.it/soluzioni/finance-intelligence",
            "image": "https://finch-ai.it/assets/images/og-image.png",
            "inLanguage": locale === 'en' ? 'en-US' : 'it-IT',
            "description": t('finance.seo.description'),
            "offers": [
                { "@type": "Offer", "name": "Demo", "price": "0", "priceCurrency": "EUR", "availability": "https://schema.org/InStock" },
                { "@type": "Offer", "name": "Starter", "price": "49", "priceCurrency": "EUR", "availability": "https://schema.org/InStock" },
                { "@type": "Offer", "name": "Professional", "price": "99", "priceCurrency": "EUR", "availability": "https://schema.org/InStock" },
                { "@type": "Offer", "name": "Business", "price": "299", "priceCurrency": "EUR", "availability": "https://schema.org/InStock" }
            ],
            "provider": { "@type": "Organization", "name": "Finch-AI S.r.l.", "url": "https://finch-ai.it" }
        },
        {
            "@context": "https://schema.org",
            "@type": "FAQPage",
            "inLanguage": locale === 'en' ? 'en-US' : 'it-IT',
            "mainEntity": (Array.isArray(faqs) ? faqs : []).map((f) => ({ "@type": "Question", "name": f.q, "acceptedAnswer": { "@type": "Answer", "text": f.a } }))
        }
    ];

    const handleWatchVideo = (e) => {
        e.preventDefault();
        const v = videoRef.current;
        if (v) {
            v.scrollIntoView({ behavior: 'smooth', block: 'center' });
            v.play().catch(() => {});
        }
    };

    return (
        <>
            <SEO
                title={t('finance.seo.title')}
                description={t('finance.seo.description')}
                keywords={t('finance.seo.keywords')}
                canonical="https://finch-ai.it/soluzioni/finance-intelligence"
                jsonLd={financeJsonLd}
            />
            <Navbar />

            <main className="sol-main">

                {/* HERO */}
                <section className="syn-hero">
                    <div className="hero-grid-bg" />
                    <div className="wrap syn-hero-in">
                        <div className="reveal in">
                            <span className="syn-pill"><span className="ping" /> {t('finance.hero.pill')}</span>
                            <h1 dangerouslySetInnerHTML={{ __html: t('finance.hero.title') }} />
                            <p className="lead">{t('finance.hero.lead')}</p>
                            <div className="syn-hero-cta">
                                <a href={APP_URL} target="_blank" rel="noopener" className="btn btn-primary"><Upload size={16} /> {t('finance.hero.ctaTry')}</a>
                                <a href={YOUTUBE_URL} target="_blank" rel="noopener" className="btn btn-ghost"><PlayCircle size={16} /> {t('finance.hero.ctaHow')}</a>
                            </div>
                            <div className="syn-trust">
                                <span className="dot"><Scale size={16} /> {t('finance.hero.trust1')}</span>
                                <span className="dot"><Lock size={16} /> {t('finance.hero.trust2')}</span>
                                <span className="dot"><Trash2 size={16} /> {t('finance.hero.trust3')}</span>
                            </div>
                        </div>
                        <div className="reveal in" style={{ transitionDelay: '.12s' }}>
                            <div className="sol-viz">
                                <div className="sol-viz-head"><span className="t">{t('finance.hero.vizHead')}</span><span className="b">{t('finance.hero.vizBadge')}</span></div>
                                <div className="sol-ring">
                                    {(Array.isArray(workflow) ? workflow : []).map((s, idx) => {
                                        const Icon = WF_ICONS[idx];
                                        return (
                                            <React.Fragment key={s.h}>
                                                <div className="sol-rnode"><span className="ri"><Icon size={18} /></span><span><span className="rl">{s.h}</span><span className="rs">{s.s}</span></span></div>
                                                {idx < workflow.length - 1 && <div className="sol-rarrow"><ArrowDown size={16} /></div>}
                                            </React.Fragment>
                                        );
                                    })}
                                </div>
                                <div className="sol-loop"><MessageSquare size={14} /> {t('finance.hero.vizLoop')}</div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* VIDEO */}
                <section className="section" id="video" style={{ paddingTop: 'clamp(40px,5vw,70px)', paddingBottom: 'clamp(40px,5vw,70px)' }}>
                    <div className="wrap reveal" style={{ maxWidth: 980 }}>
                        <div className="sol-video">
                            <video ref={videoRef} controls preload="metadata" poster="">
                                <source src="/Analista_Finanziario_Clip_01.mp4" type="video/mp4" />
                                {t('finance.video.unsupported')}
                            </video>
                            <div className="cap"><span className="pulse" /> {t('finance.video.cap')}</div>
                        </div>
                        <p style={{ textAlign: 'center', fontFamily: 'var(--mono)', fontSize: 12, color: 'var(--muted-2)', marginTop: 16 }}>
                            <a href="#video" onClick={handleWatchVideo} style={{ color: 'var(--green-deep)', fontWeight: 600 }}><Play size={12} style={{ verticalAlign: 'middle', marginRight: 5 }} />{t('finance.video.link')}</a>
                        </p>
                    </div>
                </section>

                {/* COME FUNZIONA */}
                <section className="section" style={{ background: 'var(--paper-2)' }}>
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">{t('finance.how.eyebrow')}</span>
                            <h2 className="h2">{t('finance.how.title')}</h2>
                            <p className="lead">{t('finance.how.lead')}</p>
                        </div>
                        <div className="sol-flow reveal">
                            {(Array.isArray(steps) ? steps : []).map((s, idx) => {
                                const Icon = STEP_ICONS[idx];
                                return (
                                    <React.Fragment key={s.h}>
                                        <div className="sol-step"><span className="si"><Icon size={22} /></span><span className="sk">{s.k}</span><h4>{s.h}</h4><p>{s.p}</p></div>
                                        {idx < steps.length - 1 && <div className="sol-arrow"><ArrowRight /></div>}
                                    </React.Fragment>
                                );
                            })}
                        </div>
                    </div>
                </section>

                {/* FUNZIONALITÀ */}
                <section className="section">
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">{t('finance.featuresHead.eyebrow')}</span>
                            <h2 className="h2">{t('finance.featuresHead.title')}</h2>
                            <p className="lead">{t('finance.featuresHead.lead')}</p>
                        </div>
                        <div className="syn-cap-grid reveal">
                            {(Array.isArray(features) ? features : []).map((f, idx) => {
                                const Icon = FEAT_ICONS[idx];
                                return (
                                    <div className="syn-cap" key={f.title}>
                                        <div className="syn-ic"><Icon size={24} /></div>
                                        <h3>{f.title}</h3>
                                        <p>{f.desc}</p>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                </section>

                {/* PRIVACY */}
                <section className="section" style={{ background: 'var(--paper-2)' }}>
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">{t('finance.privacyHead.eyebrow')}</span>
                            <h2 className="h2">{t('finance.privacyHead.title')}</h2>
                            <p className="lead">{t('finance.privacyHead.lead')}</p>
                        </div>
                        <div className="syn-ent reveal">
                            <div style={{ position: 'relative', zIndex: 1 }}>
                                <div className="sol-priv-head">
                                    <div className="ph"><ShieldCheck size={24} /></div>
                                    <div><div className="pn">{t('finance.privacyHead.promiseTitle')}</div><div className="ps">{t('finance.privacyHead.promiseSub')}</div></div>
                                </div>
                                <div className="sol-priv-grid">
                                    {(Array.isArray(privacy) ? privacy : []).map((p, idx) => {
                                        const Icon = PRIV_ICONS[idx];
                                        return (
                                            <div className="sol-priv" key={p.title}><span className="pi"><Icon size={18} /></span><div><div className="pt">{p.title}</div><div className="pd">{p.desc}</div></div></div>
                                        );
                                    })}
                                </div>
                                <div className="sol-priv-note"><CheckCircle size={18} /> {t('finance.privacyHead.note')}</div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* PRICING */}
                <section className="section">
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">{t('finance.pricingHead.eyebrow')}</span>
                            <h2 className="h2">{t('finance.pricingHead.title')}</h2>
                            <p className="lead">{t('finance.pricingHead.lead')}</p>
                        </div>

                        {/* tabella desktop */}
                        <div className="sol-tablewrap reveal">
                            <table className="sol-table">
                                <thead>
                                    <tr>
                                        <th>{t('finance.pricingHead.featureCol')}</th>
                                        {(Array.isArray(plans) ? plans : []).map((p) => (
                                            <th key={p.name} className={p.feat ? 'feat-col' : undefined}>
                                                {p.feat && <div className="pop">{t('finance.pricingHead.recommended')}</div>}
                                                <div className="nm">{p.name}</div>
                                                <div className="pr" style={p.price === customPrice ? { fontSize: 18 } : undefined}>{p.price}</div>
                                                <div className="per">{p.per || ' '}</div>
                                            </th>
                                        ))}
                                    </tr>
                                </thead>
                                <tbody>
                                    {(Array.isArray(rows) ? rows : []).map((r) => (
                                        <tr key={r.label}>
                                            <th>{r.label}</th>
                                            {r.vals.map((v, idx) => (
                                                <td key={idx} className={plans[idx].feat ? 'feat-col' : undefined}>{renderVal(v)}</td>
                                            ))}
                                        </tr>
                                    ))}
                                    <tr>
                                        <td />
                                        {plans.map((p) => (
                                            <td key={p.name} className={p.feat ? 'feat-col' : undefined}>
                                                <a href={APP_URL} target="_blank" rel="noopener" className={`btn ${p.feat ? 'btn-primary' : 'btn-ghost'}`} style={{ padding: '9px 16px', fontSize: 13 }}>{p.btn}</a>
                                            </td>
                                        ))}
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {/* cards mobile */}
                        <div className="sol-pcards reveal">
                            {(Array.isArray(plans) ? plans : []).map((p, pi) => (
                                <div className={`sol-plan${p.feat ? ' feat' : ''}`} key={p.name}>
                                    {p.feat && <span className="pop">{t('finance.pricingHead.recommended')}</span>}
                                    <div className="nm">{p.name}</div>
                                    <div className="pr" style={p.price === customPrice ? { fontSize: 24 } : undefined}>{p.price}</div>
                                    <div className="per">{p.per || ' '}</div>
                                    <ul>
                                        {rows.map((r) => {
                                            const v = r.vals[pi];
                                            if (v === false || v === null) return null;
                                            return <li key={r.label}><span>{r.label}</span>{v === true ? <Check /> : <b>{v}</b>}</li>;
                                        })}
                                    </ul>
                                    <a href={APP_URL} target="_blank" rel="noopener" className={`btn ${p.feat ? 'btn-primary' : 'btn-ghost'}`}>{p.btn}</a>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* ALTRE SOLUZIONI */}
                <section className="section" style={{ background: 'var(--paper-2)' }}>
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">{t('finance.related.eyebrow')}</span>
                            <h2 className="h2">{t('finance.related.title')}</h2>
                        </div>
                        <div className="sol-rel reveal">
                            <Link className="sol-relcard" to={lp('/soluzioni/document-intelligence')}>
                                <span className="tag">{t('finance.related.card1Tag')}</span>
                                <h3>{t('finance.related.card1Title')}</h3>
                                <p>{t('finance.related.card1Text')}</p>
                                <span className="go">{t('finance.related.card1Go')} <ArrowRight size={14} /></span>
                            </Link>
                            <Link className="sol-relcard" to={lp('/soluzioni/warehouse-intelligence')}>
                                <span className="tag">{t('finance.related.card2Tag')}</span>
                                <h3>{t('finance.related.card2Title')}</h3>
                                <p>{t('finance.related.card2Text')}</p>
                                <span className="go">{t('finance.related.card2Go')} <ArrowRight size={14} /></span>
                            </Link>
                        </div>
                    </div>
                </section>

                {/* CTA */}
                <section className="section" style={{ paddingTop: 0 }}>
                    <div className="wrap reveal">
                        <div className="syn-cta-box">
                            <h2>{t('finance.cta.title')}</h2>
                            <p>{t('finance.cta.text')}</p>
                            <div className="syn-cta-row">
                                <a href={APP_URL} target="_blank" rel="noopener" className="btn btn-white"><Upload size={16} /> {t('finance.cta.ctaTry')}</a>
                                <a href={YOUTUBE_URL} target="_blank" rel="noopener" className="btn btn-line"><PlayCircle size={16} /> {t('finance.cta.ctaHow')}</a>
                            </div>
                        </div>
                    </div>
                </section>

            </main>

            <Footer />
        </>
    );
}
