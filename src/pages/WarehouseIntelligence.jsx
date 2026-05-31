import React, { useEffect, useRef, useState } from 'react';
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
    Boxes,
    ShoppingCart,
    Truck,
    Banknote,
    Tag,
    Sparkles,
    Zap,
    ShieldCheck,
    Repeat,
    LineChart,
    ListChecks,
    Undo2,
    BarChart3,
    Search,
    Lightbulb,
    Building2,
    Forklift,
    Settings,
    PiggyBank,
    PlayCircle,
    Cloud,
    Users,
    LayoutDashboard,
    Grid3x3,
    AlertTriangle,
    Clock,
    Link2,
    Command,
    ChevronDown,
    ChevronLeft,
    ChevronRight,
    Maximize,
} from 'lucide-react';

const YOUTUBE_URL = 'https://www.youtube.com/@Finch-AI';

function useReveal() {
    useEffect(() => {
        const els = document.querySelectorAll('.reveal');
        const io = new IntersectionObserver(
            (entries) => entries.forEach((e) => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } }),
            { threshold: 0.12 }
        );
        els.forEach((el) => io.observe(el));
        return () => io.disconnect();
    }, []);
}

// Image metadata (language-independent); text comes from translations by index.
const SHOTS_META = [
    { src: '/assets/images/warehouse/dashboard.png', w: 1907, h: 914, icon: LayoutDashboard },
    { src: '/assets/images/warehouse/magazzino.png', w: 1904, h: 857, icon: Boxes },
    { src: '/assets/images/warehouse/vendite-offerte.png', w: 1913, h: 910, icon: Tag },
    { src: '/assets/images/warehouse/ordini.png', w: 1908, h: 850, icon: ListChecks },
    { src: '/assets/images/warehouse/approvvigionamento.png', w: 1910, h: 914, icon: ShoppingCart },
    { src: '/assets/images/warehouse/clienti.png', w: 1907, h: 900, icon: Users },
    { src: '/assets/images/warehouse/report.png', w: 1911, h: 900, icon: BarChart3 },
];

const CYCLE_ICONS = [ShoppingCart, Boxes, Tag, Truck, Banknote];
const FLOW_ICONS = [ShoppingCart, Boxes, Tag, Truck, Banknote];
const HIGHLIGHT_ICONS = [Link2, Command, Repeat, LineChart];
const MODULE_ICONS = [ShoppingCart, Boxes, Tag, ListChecks, Truck, Banknote, Undo2, BarChart3];
const ROLE_ICONS = [Building2, Tag, ShoppingCart, Forklift, Settings, PiggyBank];
const PROBLEM_ICONS = [Grid3x3, AlertTriangle, Clock];

function ScreenshotCarousel() {
    const { t } = useTranslation('solutions');
    const shots = t('warehouse.shots', { returnObjects: true });
    const arr = Array.isArray(shots) ? shots : [];
    const [i, setI] = useState(0);
    const n = SHOTS_META.length;
    const go = (idx) => setI((idx + n) % n);
    const Cur = SHOTS_META[i].icon;
    return (
        <div className="sol-carousel reveal">
            <div className="sol-stage">
                <div className="sol-chrome">
                    <i /><i /><i />
                    <span className="url">omniflow.finch-ai.it</span>
                </div>
                <div className="sol-frame">
                    {SHOTS_META.map((s, idx) => (
                        <img
                            key={s.src}
                            src={s.src}
                            alt={arr[idx]?.alt || ''}
                            width={s.w}
                            height={s.h}
                            className={idx === i ? 'on' : ''}
                            loading={idx === 0 ? 'eager' : 'lazy'}
                            decoding="async"
                            aria-hidden={idx !== i}
                        />
                    ))}
                    <button type="button" className="sol-cbtn prev" onClick={() => go(i - 1)} aria-label={t('warehouse.shotsHead.prevAria')}><ChevronLeft /></button>
                    <button type="button" className="sol-cbtn next" onClick={() => go(i + 1)} aria-label={t('warehouse.shotsHead.nextAria')}><ChevronRight /></button>
                    <div className="sol-cap">
                        <span className="ci"><Cur size={18} /></span>
                        <span><b>{arr[i]?.title}</b><span>{arr[i]?.sub}</span></span>
                    </div>
                </div>
            </div>
            <div className="sol-thumbs" role="tablist" aria-label={t('warehouse.shotsHead.carouselAria')}>
                {SHOTS_META.map((s, idx) => (
                    <button type="button" key={s.src} className={`sol-thumb${idx === i ? ' on' : ''}`} onClick={() => go(idx)} aria-label={arr[idx]?.title} aria-selected={idx === i} role="tab">
                        <img src={s.src} alt="" width={s.w} height={s.h} loading="lazy" decoding="async" />
                        <span className="tl">{arr[idx]?.title}</span>
                    </button>
                ))}
            </div>
            <div className="sol-dots">
                {SHOTS_META.map((s, idx) => (
                    <button type="button" key={s.src} className={`sol-dot${idx === i ? ' on' : ''}`} onClick={() => go(idx)} aria-label={t('warehouse.shotsHead.dotAria', { n: idx + 1 })} />
                ))}
            </div>
        </div>
    );
}

export default function WarehouseIntelligence() {
    useReveal();
    const { t } = useTranslation('solutions');
    const locale = useLocale();
    const lp = useLocalizedPath();
    const { openContact } = useContactModal();
    const [openFaq, setOpenFaq] = useState(null);
    const videoIframeRef = useRef(null);
    const arr = (v) => (Array.isArray(v) ? v : []);

    useEffect(() => { window.scrollTo(0, 0); }, []);

    const cycleSteps = t('warehouse.cycleSteps', { returnObjects: true });
    const problems = t('warehouse.problems', { returnObjects: true });
    const flowSteps = t('warehouse.flowSteps', { returnObjects: true });
    const highlights = t('warehouse.highlights', { returnObjects: true });
    const modules = t('warehouse.modules', { returnObjects: true });
    const roles = t('warehouse.roles', { returnObjects: true });
    const faqs = t('warehouse.faqs', { returnObjects: true });

    const enterFullscreen = () => {
        const el = videoIframeRef.current;
        if (!el) return;
        const req = el.requestFullscreen || el.webkitRequestFullscreen || el.msRequestFullscreen;
        if (req) req.call(el);
    };

    const warehouseJsonLd = [
        {
            "@context": "https://schema.org",
            "@type": "SoftwareApplication",
            "name": "OmniFlow — Warehouse Intelligence by Finch-AI",
            "alternateName": ["OmniFlow", "Finch-AI Warehouse Intelligence"],
            "applicationCategory": "BusinessApplication",
            "applicationSubCategory": "WarehouseManagement",
            "operatingSystem": "Web, Cloud, On-premise",
            "url": "https://finch-ai.it/soluzioni/warehouse-intelligence",
            "image": "https://finch-ai.it/assets/images/warehouse/dashboard.png",
            "screenshot": "https://finch-ai.it/assets/images/warehouse/dashboard.png",
            "description": t('warehouse.seo.ldDescription'),
            "offers": { "@type": "Offer", "price": "0", "priceCurrency": "EUR", "availability": "https://schema.org/InStock", "url": "https://finch-ai.it/#contatti" },
            "provider": { "@type": "Organization", "name": "Finch-AI S.r.l.", "url": "https://finch-ai.it", "logo": "https://finch-ai.it/assets/images/LOGO.png" },
            "inLanguage": locale === 'en' ? 'en-US' : 'it-IT'
        },
        {
            "@context": "https://schema.org",
            "@type": "FAQPage",
            "inLanguage": locale === 'en' ? 'en-US' : 'it-IT',
            "mainEntity": arr(faqs).map((f) => ({ "@type": "Question", "name": f.q, "acceptedAnswer": { "@type": "Answer", "text": f.a } }))
        }
    ];

    return (
        <>
            <SEO
                title={t('warehouse.seo.title')}
                description={t('warehouse.seo.description')}
                keywords={t('warehouse.seo.keywords')}
                canonical="https://finch-ai.it/soluzioni/warehouse-intelligence"
                ogImage="https://finch-ai.it/assets/images/warehouse/dashboard.png"
                ogImageAlt={t('warehouse.seo.ogImageAlt')}
                jsonLd={warehouseJsonLd}
            />
            <Navbar />

            <main className="sol-main">

                {/* HERO */}
                <section className="syn-hero">
                    <div className="hero-grid-bg" />
                    <div className="wrap syn-hero-in">
                        <div className="reveal in">
                            <span className="syn-pill"><span className="ping" /> {t('warehouse.hero.pill')}</span>
                            <h1 dangerouslySetInnerHTML={{ __html: t('warehouse.hero.title') }} />
                            <p className="lead" dangerouslySetInnerHTML={{ __html: t('warehouse.hero.lead') }} />
                            <div className="syn-hero-cta">
                                <button type="button" className="btn btn-primary" onClick={() => openContact({ prefill: { need: t('warehouse.hero.demoPrefill') } })}>{t('warehouse.hero.ctaDemo')} <ArrowRight size={16} /></button>
                                <a href={YOUTUBE_URL} target="_blank" rel="noopener" className="btn btn-ghost"><PlayCircle size={16} /> {t('warehouse.hero.ctaHow')}</a>
                            </div>
                            <div className="syn-trust">
                                <span className="dot"><Sparkles size={16} /> {t('warehouse.hero.trust1')}</span>
                                <span className="dot"><Boxes size={16} /> {t('warehouse.hero.trust2')}</span>
                                <span className="dot"><Cloud size={16} /> {t('warehouse.hero.trust3')}</span>
                                <span className="dot"><ShieldCheck size={16} /> {t('warehouse.hero.trust4')}</span>
                            </div>
                        </div>
                        <div className="reveal in" style={{ transitionDelay: '.12s' }}>
                            <div className="sol-viz">
                                <div className="sol-viz-head"><span className="t">{t('warehouse.hero.vizHead')}</span><span className="b">{t('warehouse.hero.vizBadge')}</span></div>
                                <div className="sol-ring">
                                    {arr(cycleSteps).map((s, idx) => {
                                        const Icon = CYCLE_ICONS[idx];
                                        return (
                                            <React.Fragment key={s.label}>
                                                <div className="sol-rnode"><span className="ri"><Icon size={18} /></span><span><span className="rl">{s.label}</span><span className="rs">{s.sub}</span></span></div>
                                                {idx < cycleSteps.length - 1 && <div className="sol-rarrow"><ArrowDown size={16} /></div>}
                                            </React.Fragment>
                                        );
                                    })}
                                </div>
                                <div className="sol-loop"><Repeat size={14} /> {t('warehouse.hero.vizLoop')}</div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* PROBLEMA */}
                <section className="section" style={{ paddingTop: 'clamp(40px,6vw,80px)' }}>
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">{t('warehouse.problemHead.eyebrow')}</span>
                            <h2 className="h2">{t('warehouse.problemHead.title')}</h2>
                            <p className="lead">{t('warehouse.problemHead.lead')}</p>
                        </div>
                        <div className="syn-grid-3">
                            {arr(problems).map((p, idx) => {
                                const Icon = PROBLEM_ICONS[idx];
                                return (
                                    <div className="syn-cardbox reveal" key={p.title} style={{ transitionDelay: `${idx * 0.1}s` }}>
                                        <div className="syn-ic"><Icon size={22} /></div>
                                        <h3>{p.title}</h3>
                                        <p>{p.desc}</p>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                </section>

                {/* SOLUZIONE / CICLO */}
                <section className="section" style={{ background: 'var(--paper-2)' }}>
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">{t('warehouse.solutionHead.eyebrow')}</span>
                            <h2 className="h2">{t('warehouse.solutionHead.title')}</h2>
                            <p className="lead">{t('warehouse.solutionHead.lead')}</p>
                        </div>
                        <div className="sol-flow reveal" style={{ marginBottom: 40 }}>
                            {arr(flowSteps).map((s, idx) => {
                                const Icon = FLOW_ICONS[idx];
                                return (
                                    <React.Fragment key={s.h}>
                                        <div className="sol-step"><span className="si"><Icon size={22} /></span><span className="sk">{s.k}</span><h4>{s.h}</h4><p>{s.p}</p></div>
                                        {idx < flowSteps.length - 1 && <div className="sol-arrow"><ArrowRight /></div>}
                                    </React.Fragment>
                                );
                            })}
                        </div>
                        <div className="syn-cap-grid reveal">
                            {arr(highlights).map((h, idx) => {
                                const Icon = HIGHLIGHT_ICONS[idx];
                                return (
                                    <div className="syn-cap" key={h.tag}>
                                        <span className="num">{h.tag}</span>
                                        <div className="syn-ic"><Icon size={24} /></div>
                                        <h3>{h.title}</h3>
                                        <p>{h.desc}</p>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                </section>

                {/* MODULI */}
                <section className="section">
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">{t('warehouse.modulesHead.eyebrow')}</span>
                            <h2 className="h2">{t('warehouse.modulesHead.title')}</h2>
                            <p className="lead">{t('warehouse.modulesHead.lead')}</p>
                        </div>
                        <div className="sol-grid-4 reveal">
                            {arr(modules).map((m, idx) => {
                                const Icon = MODULE_ICONS[idx];
                                return (
                                    <div className="syn-cardbox" key={m.title}>
                                        <div className="syn-ic"><Icon size={22} /></div>
                                        <h3 style={{ fontSize: 18 }}>{m.title}</h3>
                                        <p>{m.desc}</p>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                </section>

                {/* AI ASSISTANT */}
                <section className="section" style={{ background: 'var(--paper-2)' }}>
                    <div className="wrap">
                        <div className="syn-ent reveal">
                            <div className="syn-ent-in">
                                <div>
                                    <span className="eyebrow on-dark" style={{ marginBottom: 16 }}>{t('warehouse.assistant.eyebrow')}</span>
                                    <h2 dangerouslySetInnerHTML={{ __html: t('warehouse.assistant.title') }} />
                                    <p>{t('warehouse.assistant.text')}</p>
                                    <div className="syn-ent-feats">
                                        <div className="syn-ent-feat"><span className="fi"><Search size={20} /></span><div><div className="ft">{t('warehouse.assistant.feat1Title')}</div><div className="fd">{t('warehouse.assistant.feat1Desc')}</div></div></div>
                                        <div className="syn-ent-feat"><span className="fi"><Zap size={20} /></span><div><div className="ft">{t('warehouse.assistant.feat2Title')}</div><div className="fd">{t('warehouse.assistant.feat2Desc')}</div></div></div>
                                        <div className="syn-ent-feat"><span className="fi"><Lightbulb size={20} /></span><div><div className="ft">{t('warehouse.assistant.feat3Title')}</div><div className="fd">{t('warehouse.assistant.feat3Desc')}</div></div></div>
                                        <div className="syn-ent-feat"><span className="fi"><ShieldCheck size={20} /></span><div><div className="ft">{t('warehouse.assistant.feat4Title')}</div><div className="fd">{t('warehouse.assistant.feat4Desc')}</div></div></div>
                                    </div>
                                </div>
                                <div>
                                    <div className="syn-card" style={{ background: '#0d1f17', borderColor: 'rgba(255,255,255,.1)' }}>
                                        <div className="topbar"><i /><i /><i /><span className="lbl" style={{ color: 'rgba(255,255,255,.5)' }}>OmniFlow AI</span></div>
                                        <div className="syn-chat"><div className="av syn-av-user">{t('warehouse.assistant.you')}</div><div className="syn-bub" style={{ background: 'rgba(255,255,255,.06)', borderColor: 'rgba(255,255,255,.12)', color: '#fff' }}>{t('warehouse.assistant.chatQ1')}</div></div>
                                        <div className="syn-chat"><div className="av syn-av-ai">AI</div><div className="syn-bub" style={{ background: 'rgba(255,255,255,.06)', borderColor: 'rgba(255,255,255,.12)', color: '#fff' }}>{t('warehouse.assistant.chatA1Pre')} <strong>{t('warehouse.assistant.chatA1Bold')}</strong>{t('warehouse.assistant.chatA1Post')}</div></div>
                                        <div className="syn-chat"><div className="av syn-av-user">{t('warehouse.assistant.you')}</div><div className="syn-bub" style={{ background: 'rgba(255,255,255,.06)', borderColor: 'rgba(255,255,255,.12)', color: '#fff' }}>{t('warehouse.assistant.chatQ2')}</div></div>
                                        <div className="syn-chat"><div className="av syn-av-ai">AI</div><div className="syn-bub" style={{ background: 'rgba(255,255,255,.06)', borderColor: 'rgba(255,255,255,.12)', color: '#fff' }}>{t('warehouse.assistant.chatA2Pre')} <strong>{t('warehouse.assistant.chatA2Bold')}</strong> {t('warehouse.assistant.chatA2Post')}</div></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* RUOLI */}
                <section className="section">
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">{t('warehouse.rolesHead.eyebrow')}</span>
                            <h2 className="h2">{t('warehouse.rolesHead.title')}</h2>
                        </div>
                        <div className="syn-grid-3 reveal">
                            {arr(roles).map((r, idx) => {
                                const Icon = ROLE_ICONS[idx];
                                return (
                                    <div className="syn-cardbox" key={r.title}>
                                        <div className="syn-ic"><Icon size={22} /></div>
                                        <h3>{r.title}</h3>
                                        <p>{r.desc}</p>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                </section>

                {/* DENTRO OMNIFLOW — carousel */}
                <section className="section" style={{ background: 'var(--paper-2)' }}>
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">{t('warehouse.shotsHead.eyebrow')}</span>
                            <h2 className="h2">{t('warehouse.shotsHead.title')}</h2>
                            <p className="lead">{t('warehouse.shotsHead.lead')}</p>
                        </div>
                        <ScreenshotCarousel />
                    </div>
                </section>

                {/* VIDEO DEMO */}
                <section className="section" id="come-funziona" style={{ paddingTop: 0 }}>
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">{t('warehouse.videoHead.eyebrow')}</span>
                            <h2 className="h2">{t('warehouse.videoHead.title')}</h2>
                            <p className="lead">{t('warehouse.videoHead.lead')}</p>
                        </div>
                        <div className="reveal" style={{ maxWidth: 960, margin: '0 auto' }}>
                            <div className="sol-video">
                                <iframe ref={videoIframeRef} src={locale === 'en' ? "/assets/videos/warehouse-intelligence-demo-en.html" : "/assets/videos/warehouse-intelligence-demo.html"} title={t('warehouse.videoHead.videoTitle')} allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen" allowFullScreen />
                                <button type="button" onClick={enterFullscreen} className="fs" aria-label={t('warehouse.videoHead.fullscreen')}><Maximize size={15} /> {t('warehouse.videoHead.fullscreen')}</button>
                            </div>
                        </div>
                    </div>
                </section>

                {/* FAQ */}
                <section className="section" style={{ paddingTop: 0 }}>
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">{t('warehouse.faqHead.eyebrow')}</span>
                            <h2 className="h2">{t('warehouse.faqHead.title')}</h2>
                        </div>
                        <div className="sol-faq reveal">
                            {arr(faqs).map((item, i) => (
                                <div className={`sol-q${openFaq === i ? ' open' : ''}`} key={item.q}>
                                    <button type="button" className="sol-qbtn" onClick={() => setOpenFaq(openFaq === i ? null : i)} aria-expanded={openFaq === i}>
                                        {item.q}<span className="chev"><ChevronDown /></span>
                                    </button>
                                    {openFaq === i && <div className="sol-a">{item.a}</div>}
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* ALTRE SOLUZIONI */}
                <section className="section" style={{ paddingTop: 0 }}>
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">{t('warehouse.related.eyebrow')}</span>
                            <h2 className="h2">{t('warehouse.related.title')}</h2>
                        </div>
                        <div className="sol-rel reveal">
                            <Link className="sol-relcard" to={lp('/soluzioni/document-intelligence')}>
                                <span className="tag">{t('warehouse.related.card1Tag')}</span>
                                <h3>{t('warehouse.related.card1Title')}</h3>
                                <p>{t('warehouse.related.card1Text')}</p>
                                <span className="go">{t('warehouse.related.card1Go')} <ArrowRight size={14} /></span>
                            </Link>
                            <Link className="sol-relcard" to={lp('/soluzioni/finance-intelligence')}>
                                <span className="tag">{t('warehouse.related.card2Tag')}</span>
                                <h3>{t('warehouse.related.card2Title')}</h3>
                                <p>{t('warehouse.related.card2Text')}</p>
                                <span className="go">{t('warehouse.related.card2Go')} <ArrowRight size={14} /></span>
                            </Link>
                        </div>
                    </div>
                </section>

                {/* CTA FINALE */}
                <section className="section" style={{ paddingTop: 0 }}>
                    <div className="wrap reveal">
                        <div className="syn-cta-box">
                            <h2>{t('warehouse.cta.title')}</h2>
                            <p>{t('warehouse.cta.text')}</p>
                            <div className="syn-cta-row">
                                <button type="button" className="btn btn-white" onClick={() => openContact({ prefill: { need: t('warehouse.cta.demoPrefill') } })}>{t('warehouse.cta.ctaDemo')} <ArrowRight size={16} /></button>
                                <a href={YOUTUBE_URL} target="_blank" rel="noopener" className="btn btn-line"><PlayCircle size={16} /> {t('warehouse.cta.ctaHow')}</a>
                            </div>
                        </div>
                    </div>
                </section>

            </main>

            <Footer />
        </>
    );
}
