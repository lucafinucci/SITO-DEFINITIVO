import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import SEO from '@/components/SEO';
import Navbar from '@/components/Navbar';
import Footer from '@/components/Footer';
import VideoModal from '@/components/VideoModal';
import { useContactModal } from '@/context/ContactModalContext';
import { useLocale, useLocalizedPath } from '@/i18n/routing';
import '@/styles/synapse.css';
import '@/styles/solutions.css';
import {
    ArrowRight,
    ArrowDown,
    FileText,
    Download,
    CheckCircle,
    Cpu,
    CheckSquare,
    ShieldCheck,
    PlugZap,
    Zap,
    PlayCircle,
    Sparkles,
    PlusCircle,
    Calculator,
    Tag,
    Layers,
    Camera,
    Wifi,
    Battery,
    ReceiptText,
    Receipt,
    IdCard,
    Truck,
    Landmark,
    ShoppingCart,
    BadgeMinus,
    HeartPulse,
    Contact,
    UserCheck,
    ScanLine,
    ChevronDown,
    Check,
    Minus,
} from 'lucide-react';

const YOUTUBE_URL = 'https://www.youtube.com/@Finch-AI';
const APP_URL = 'https://documentintelligence.finch-ai.it/';

const PIPELINE_ICONS = [Download, Cpu, CheckSquare, PlugZap];
const DOCTYPE_ICONS = [ReceiptText, Receipt, IdCard, Truck, Landmark, ShoppingCart, BadgeMinus, HeartPulse, Contact];
const RULE_ICONS = [PlusCircle, Calculator, Tag];
const FEAT_ICONS = [CheckCircle, Cpu, UserCheck, PlugZap, Layers, ShieldCheck];

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

export default function DocumentIntelligence() {
    useReveal();
    const { t } = useTranslation('solutions');
    const locale = useLocale();
    const lp = useLocalizedPath();
    const { openContact } = useContactModal();
    const [openFaq, setOpenFaq] = useState(null);
    const [isVideoModalOpen, setIsVideoModalOpen] = useState(false);

    useEffect(() => { window.scrollTo(0, 0); }, []);

    const pipeline = t('document.pipeline', { returnObjects: true });
    const docTypes = t('document.docTypes', { returnObjects: true });
    const ruleCards = t('document.ruleCards', { returnObjects: true });
    const features = t('document.features', { returnObjects: true });
    const plans = t('document.plans', { returnObjects: true });
    const faqs = t('document.faqs', { returnObjects: true });
    const arr = (v) => (Array.isArray(v) ? v : []);

    const docJsonLd = [
        {
            "@context": "https://schema.org",
            "@type": "SoftwareApplication",
            "name": "Finch-AI Document Intelligence",
            "applicationCategory": "BusinessApplication",
            "applicationSubCategory": "DocumentManagement",
            "operatingSystem": "Web, Android",
            "url": "https://finch-ai.it/soluzioni/document-intelligence",
            "image": "https://finch-ai.it/assets/images/og-image.png",
            "inLanguage": locale === 'en' ? 'en-US' : 'it-IT',
            "description": t('document.seo.description'),
            "offers": [
                { "@type": "Offer", "name": "Demo", "price": "0", "priceCurrency": "EUR", "availability": "https://schema.org/InStock" },
                { "@type": "Offer", "name": "Basic", "price": "49", "priceCurrency": "EUR", "availability": "https://schema.org/InStock" },
                { "@type": "Offer", "name": "Business", "price": "129", "priceCurrency": "EUR", "availability": "https://schema.org/InStock" },
                { "@type": "Offer", "name": "Professional", "price": "249", "priceCurrency": "EUR", "availability": "https://schema.org/InStock" }
            ],
            "provider": { "@type": "Organization", "name": "Finch-AI S.r.l.", "url": "https://finch-ai.it" }
        },
        {
            "@context": "https://schema.org",
            "@type": "HowTo",
            "inLanguage": locale === 'en' ? 'en-US' : 'it-IT',
            "name": t('document.how.title'),
            "step": arr(pipeline).map((s, idx) => ({ "@type": "HowToStep", "position": idx + 1, "name": s.h, "text": s.p }))
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
                title={t('document.seo.title')}
                description={t('document.seo.description')}
                keywords={t('document.seo.keywords')}
                canonical="https://finch-ai.it/soluzioni/document-intelligence"
                jsonLd={docJsonLd}
            />
            <Navbar />

            <main className="sol-main">

                {/* HERO */}
                <section className="syn-hero">
                    <div className="hero-grid-bg" />
                    <div className="wrap syn-hero-in">
                        <div className="reveal in">
                            <span className="syn-pill"><span className="ping" /> {t('document.hero.pill')}</span>
                            <h1 dangerouslySetInnerHTML={{ __html: t('document.hero.title') }} />
                            <p className="lead">{t('document.hero.lead')}</p>
                            <div className="syn-hero-cta">
                                <a href={APP_URL} target="_blank" rel="noopener" className="btn btn-primary">{t('document.hero.ctaTry')} <ArrowRight size={16} /></a>
                                <a href={YOUTUBE_URL} target="_blank" rel="noopener" className="btn btn-ghost"><PlayCircle size={16} /> {t('document.hero.ctaHow')}</a>
                            </div>
                            <div className="syn-trust">
                                <span className="dot"><ShieldCheck size={16} /> {t('document.hero.trust1')}</span>
                                <span className="dot"><Camera size={16} /> {t('document.hero.trust2')}</span>
                                <span className="dot"><PlugZap size={16} /> {t('document.hero.trust3')}</span>
                            </div>
                        </div>
                        <div className="reveal in" style={{ transitionDelay: '.12s' }}>
                            <div className="sol-viz">
                                <div className="sol-viz-head"><span className="t">{t('document.hero.vizHead')}</span><span className="b">{t('document.hero.vizBadge')}</span></div>
                                <div className="sol-ring">
                                    {arr(pipeline).map((s, idx) => {
                                        const Icon = PIPELINE_ICONS[idx];
                                        return (
                                            <React.Fragment key={s.h}>
                                                <div className="sol-rnode"><span className="ri"><Icon size={18} /></span><span><span className="rl">{s.h}</span><span className="rs">{s.p.split('.')[0]}</span></span></div>
                                                {idx < pipeline.length - 1 && <div className="sol-rarrow"><ArrowDown size={16} /></div>}
                                            </React.Fragment>
                                        );
                                    })}
                                </div>
                                <div className="sol-loop"><Sparkles size={14} /> {t('document.hero.vizLoop')}</div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* BROCHURE */}
                <section className="section" style={{ paddingTop: 0, paddingBottom: 'clamp(40px,5vw,70px)' }}>
                    <div className="wrap reveal">
                        <div className="sol-bar">
                            <div className="l"><span className="ic"><FileText size={22} /></span><div><h3>{t('document.brochure.title')}</h3><p>{t('document.brochure.text')}</p></div></div>
                            <div className="r">
                                <a href="/it.pdf" download="Brochure-Document-Intelligence-IT.pdf" className="btn btn-primary"><Download size={16} /> {t('document.brochure.it')}</a>
                                <a href="/en.pdf" download="Brochure-Document-Intelligence-EN.pdf" className="btn btn-ghost"><Download size={16} /> {t('document.brochure.en')}</a>
                            </div>
                        </div>
                    </div>
                </section>

                {/* COME FUNZIONA + INFOGRAFICA */}
                <section className="section" id="come-funziona" style={{ background: 'var(--paper-2)' }}>
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">{t('document.how.eyebrow')}</span>
                            <h2 className="h2">{t('document.how.title')}</h2>
                            <p className="lead">{t('document.how.lead')}</p>
                        </div>
                        <div className="sol-flow reveal" style={{ marginBottom: 32 }}>
                            {arr(pipeline).map((s, idx) => {
                                const Icon = PIPELINE_ICONS[idx];
                                return (
                                    <React.Fragment key={s.h}>
                                        <div className="sol-step"><span className="si"><Icon size={22} /></span><span className="sk">{s.k}</span><h4>{s.h}</h4><p>{s.p}</p></div>
                                        {idx < pipeline.length - 1 && <div className="sol-arrow"><ArrowRight /></div>}
                                    </React.Fragment>
                                );
                            })}
                        </div>
                        <div className="sol-shot reveal">
                            <picture>
                                <source srcSet="/assets/images/infografica_document_intelligence.webp" type="image/webp" />
                                <img src="/assets/images/infografica_document_intelligence.png" alt={t('document.how.imgAlt')} width="2708" height="1480" loading="lazy" decoding="async" />
                            </picture>
                        </div>
                    </div>
                </section>

                {/* TIPOLOGIE */}
                <section className="section">
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">{t('document.docTypesHead.eyebrow')}</span>
                            <h2 className="h2">{t('document.docTypesHead.title')}</h2>
                            <p className="lead">{t('document.docTypesHead.lead')}</p>
                        </div>
                        <div className="syn-grid-3 reveal">
                            {arr(docTypes).map((d, idx) => {
                                const Icon = DOCTYPE_ICONS[idx];
                                return (
                                    <div className="syn-cardbox" key={d.title}>
                                        <div className="syn-ic"><Icon size={22} /></div>
                                        <span className="sol-tag"><Layers size={12} /> {d.tag}</span>
                                        <h3 style={{ fontSize: 19 }}>{d.title}</h3>
                                        <p>{d.desc}</p>
                                    </div>
                                );
                            })}
                        </div>
                        <div className="syn-ent reveal" style={{ marginTop: 28 }}>
                            <div className="syn-ent-in" style={{ gridTemplateColumns: '1fr auto', alignItems: 'center' }}>
                                <div>
                                    <span className="eyebrow on-dark" style={{ marginBottom: 14 }}>{t('document.customModel.eyebrow')}</span>
                                    <h2 style={{ marginBottom: 8 }} dangerouslySetInnerHTML={{ __html: t('document.customModel.title') }} />
                                    <p style={{ marginBottom: 0 }}>{t('document.customModel.text')}</p>
                                </div>
                                <div><a href={APP_URL} target="_blank" rel="noopener" className="btn btn-lime">{t('document.customModel.cta')} <ArrowRight size={16} /></a></div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* REGOLE */}
                <section className="section" style={{ background: 'var(--paper-2)' }}>
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">{t('document.rulesHead.eyebrow')}</span>
                            <h2 className="h2">{t('document.rulesHead.title')}</h2>
                            <p className="lead">{t('document.rulesHead.lead')}</p>
                        </div>
                        <div className="sol-grid-2 reveal" style={{ alignItems: 'start' }}>
                            <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
                                {arr(ruleCards).map((r, idx) => {
                                    const Icon = RULE_ICONS[idx];
                                    return (
                                        <div className="syn-cardbox" key={r.title}>
                                            <div style={{ display: 'flex', gap: 16, alignItems: 'flex-start' }}>
                                                <div className="syn-ic" style={{ marginBottom: 0, flex: 'none' }}><Icon size={22} /></div>
                                                <div>
                                                    <h3 style={{ fontSize: 19 }}>{r.title}</h3>
                                                    <p style={{ marginBottom: 10 }}>{r.desc}</p>
                                                    <div className="sol-rule" style={{ boxShadow: 'none' }}><div className="body" style={{ padding: 14 }}><p className="quote">{r.example}</p></div></div>
                                                </div>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                            <div className="sol-rule">
                                <div className="chrome"><i /><i /><i /><span className="lbl">{t('document.ruleDemo.label')}</span></div>
                                <div className="body">
                                    <div>
                                        <div className="sol-rkey"><CheckCircle /> {t('document.ruleDemo.active')}</div>
                                        <div className="active"><div className="fld">{t('document.ruleDemo.field')} · centro_di_costo</div><p className="quote">{t('document.ruleDemo.ruleExample')}</p><span className="sol-chip-ok"><Check /> {t('document.ruleDemo.applied')}</span></div>
                                    </div>
                                    <div>
                                        <div className="sol-rkey"><PlusCircle /> {t('document.ruleDemo.newRule')}</div>
                                        <div className="draft">
                                            <div className="fld">{t('document.ruleDemo.field')} · fornitore_strategico</div>
                                            <div className="input">{t('document.ruleDemo.draftInput')}<span className="sol-cursor" /></div>
                                            <div className="sol-draft-foot"><span className="hint"><Sparkles /> {t('document.ruleDemo.aiUnderstands')}</span><span className="btn btn-primary" style={{ padding: '8px 16px', fontSize: 13 }}>{t('document.ruleDemo.saveRule')}</span></div>
                                        </div>
                                    </div>
                                    <div className="out">
                                        <div className="sol-rkey"><PlugZap /> {t('document.ruleDemo.outputPreview')}</div>
                                        <div className="sol-out-grid">
                                            <div className="sol-out-cell"><div className="k">importo_totale</div><div className="v">€ 7.450,00</div></div>
                                            <div className="sol-out-cell"><div className="k">imponibile_netto</div><div className="v">€ 6.803,28</div></div>
                                            <div className="sol-out-cell"><div className="k">centro_di_costo</div><div className="v">CC-0042</div></div>
                                            <div className="sol-out-cell hi"><div className="k">fornitore_strategico</div><div className="v">{t('document.ruleDemo.strategicValue')}</div></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* APP ANDROID */}
                <section className="section">
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">{t('document.androidHead.eyebrow')}</span>
                            <h2 className="h2">{t('document.androidHead.title')}</h2>
                        </div>
                        <div className="syn-ent reveal">
                            <div className="syn-ent-in">
                                <div>
                                    <span className="eyebrow on-dark" style={{ marginBottom: 14 }}>{t('document.android.eyebrow')}</span>
                                    <h2 dangerouslySetInnerHTML={{ __html: t('document.android.title') }} />
                                    <p>{t('document.android.text')}</p>
                                    <div className="syn-ent-feats">
                                        <div className="syn-ent-feat"><span className="fi"><Camera size={20} /></span><div><div className="ft">{t('document.android.feat1Title')}</div><div className="fd">{t('document.android.feat1Desc')}</div></div></div>
                                        <div className="syn-ent-feat"><span className="fi"><Wifi size={20} /></span><div><div className="ft">{t('document.android.feat2Title')}</div><div className="fd">{t('document.android.feat2Desc')}</div></div></div>
                                        <div className="syn-ent-feat"><span className="fi"><CheckSquare size={20} /></span><div><div className="ft">{t('document.android.feat3Title')}</div><div className="fd">{t('document.android.feat3Desc')}</div></div></div>
                                        <div className="syn-ent-feat"><span className="fi"><Zap size={20} /></span><div><div className="ft">{t('document.android.feat4Title')}</div><div className="fd">{t('document.android.feat4Desc')}</div></div></div>
                                    </div>
                                    <a href={APP_URL} target="_blank" rel="noopener" className="btn btn-lime" style={{ marginTop: 24 }}>{t('document.android.cta')} <ArrowRight size={16} /></a>
                                </div>
                                <div className="sol-phone-wrap">
                                    <div className="sol-phone">
                                        <div className="badge"><ScanLine size={12} /> Android</div>
                                        <div className="screen">
                                            <div className="sbar"><span>9:41</span><span style={{ display: 'flex', gap: 5 }}><Wifi size={11} /><Battery size={11} /></span></div>
                                            <div className="ahead"><ScanLine size={15} /> Document Intelligence</div>
                                            <div className="view"><span className="br tl" /><span className="br tr" /><span className="br bl" /><span className="br brr" /><span className="scan" /><FileText size={38} style={{ color: 'rgba(255,255,255,.18)' }} /></div>
                                            <div className="data">
                                                <div className="ok"><CheckCircle size={12} /> {t('document.android.phoneExtracted')}</div>
                                                <div className="row"><span className="k">{t('document.android.phoneSupplier')}</span><span className="v">Magazzini Nord SRL</span></div>
                                                <div className="row"><span className="k">{t('document.android.phoneDdt')}</span><span className="v">2025/00391</span></div>
                                                <div className="row"><span className="k">{t('document.android.phoneItems')}</span><span className="v">{t('document.android.phoneItemsVal')}</span></div>
                                                <div className="pcta">{t('document.android.phoneCta')}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* VIDEO DEMO */}
                <section className="section" style={{ background: 'var(--paper-2)' }}>
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">{t('document.videoHead.eyebrow')}</span>
                            <h2 className="h2">{t('document.videoHead.title')}</h2>
                        </div>
                        <div className="reveal" style={{ maxWidth: 960, margin: '0 auto' }}>
                            <div className="sol-video">
                                <iframe src="/assets/videos/document-intelligence-demo.html" title={t('document.videoHead.videoTitle')} allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowFullScreen />
                                <button type="button" className="fs" onClick={() => setIsVideoModalOpen(true)} aria-label={t('document.videoHead.openAria')}><PlayCircle size={15} /> {t('document.videoHead.open')}</button>
                            </div>
                        </div>
                    </div>
                </section>

                {/* FUNZIONALITÀ */}
                <section className="section">
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">{t('document.featuresHead.eyebrow')}</span>
                            <h2 className="h2">{t('document.featuresHead.title')}</h2>
                        </div>
                        <div className="syn-grid-3 reveal">
                            {arr(features).map((f, idx) => {
                                const Icon = FEAT_ICONS[idx];
                                return (
                                    <div className="syn-cardbox" key={f.title}>
                                        <div className="syn-ic"><Icon size={22} /></div>
                                        <h3 style={{ fontSize: 19 }}>{f.title}</h3>
                                        <p>{f.desc}</p>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                </section>

                {/* PRICING */}
                <section className="section" style={{ background: 'var(--paper-2)' }}>
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">{t('document.pricingHead.eyebrow')}</span>
                            <h2 className="h2">{t('document.pricingHead.title')}</h2>
                            <p className="lead">{t('document.pricingHead.lead')}</p>
                        </div>
                        <div className="sol-price-grid reveal">
                            {arr(plans).map((p) => (
                                <div className={`sol-plan${p.feat ? ' feat' : ''}`} key={p.name}>
                                    {p.feat && <span className="pop">{t('document.pricingHead.popular')}</span>}
                                    <div className="nm">{p.name}</div>
                                    <div className="pr">{p.price}</div>
                                    <div className="per">{p.per}</div>
                                    <p className="desc">{p.desc}</p>
                                    <ul>
                                        {p.rows.map(([k, v]) => (
                                            <li key={k}><span>{k}</span>{typeof v === 'boolean' ? (v ? <Check /> : <Minus className="no" />) : <b>{v}</b>}</li>
                                        ))}
                                    </ul>
                                    <a href={APP_URL} target="_blank" rel="noopener" className={`btn ${p.feat ? 'btn-primary' : 'btn-ghost'}`}>{p.btn}</a>
                                </div>
                            ))}
                        </div>
                        <div className="sol-price-note">
                            <span><Sparkles size={14} style={{ verticalAlign: 'middle', marginRight: 6, color: 'var(--green)' }} /> {t('document.pricingHead.note1')}</span>
                            <span>{t('document.pricingHead.note2')}</span>
                        </div>
                    </div>
                </section>

                {/* FAQ */}
                <section className="section">
                    <div className="wrap">
                        <div className="syn-sec-head reveal">
                            <span className="eyebrow center">{t('document.faqHead.eyebrow')}</span>
                            <h2 className="h2">{t('document.faqHead.title')}</h2>
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
                            <span className="eyebrow center">{t('document.related.eyebrow')}</span>
                            <h2 className="h2">{t('document.related.title')}</h2>
                        </div>
                        <div className="sol-rel reveal">
                            <Link className="sol-relcard" to={lp('/soluzioni/warehouse-intelligence')}>
                                <span className="tag">{t('document.related.card1Tag')}</span>
                                <h3>{t('document.related.card1Title')}</h3>
                                <p>{t('document.related.card1Text')}</p>
                                <span className="go">{t('document.related.card1Go')} <ArrowRight size={14} /></span>
                            </Link>
                            <Link className="sol-relcard" to={lp('/soluzioni/finance-intelligence')}>
                                <span className="tag">{t('document.related.card2Tag')}</span>
                                <h3>{t('document.related.card2Title')}</h3>
                                <p>{t('document.related.card2Text')}</p>
                                <span className="go">{t('document.related.card2Go')} <ArrowRight size={14} /></span>
                            </Link>
                        </div>
                    </div>
                </section>

                {/* CTA */}
                <section className="section" style={{ paddingTop: 0 }}>
                    <div className="wrap reveal">
                        <div className="syn-cta-box">
                            <h2>{t('document.cta.title')}</h2>
                            <p>{t('document.cta.text')}</p>
                            <div className="syn-cta-row">
                                <a href={APP_URL} target="_blank" rel="noopener" className="btn btn-white">{t('document.cta.ctaTry')} <ArrowRight size={16} /></a>
                                <a href={YOUTUBE_URL} target="_blank" rel="noopener" className="btn btn-line"><PlayCircle size={16} /> {t('document.cta.ctaHow')}</a>
                            </div>
                        </div>
                    </div>
                </section>

            </main>

            <Footer />

            <VideoModal
                isOpen={isVideoModalOpen}
                onClose={() => setIsVideoModalOpen(false)}
                videoUrl="/assets/videos/document-intelligence-demo.html"
                title={t('document.videoHead.modalTitle')}
            />
        </>
    );
}
