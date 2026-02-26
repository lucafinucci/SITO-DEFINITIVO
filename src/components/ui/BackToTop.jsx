import { useState, useEffect } from "react";

export default function BackToTop() {
    const [isVisible, setIsVisible] = useState(false);

    useEffect(() => {
        const toggleVisibility = () => {
            if (window.scrollY > 300) {
                setIsVisible(true);
            } else {
                setIsVisible(false);
            }
        };

        window.addEventListener("scroll", toggleVisibility);
        return () => window.removeEventListener("scroll", toggleVisibility);
    }, []);

    const scrollToTop = () => {
        window.scrollTo({
            top: 0,
            behavior: "smooth",
        });
    };

    return (
        <button
            onClick={scrollToTop}
            className={`fixed bottom-8 right-8 z-50 p-3 rounded-full bg-cyan-500 text-white shadow-[0_0_20px_rgba(6,182,212,0.4)] transition-all duration-300 hover:bg-cyan-400 hover:scale-110 active:scale-95 hover:shadow-[0_0_30px_rgba(6,182,212,0.6)] ${isVisible ? "opacity-100 translate-y-0" : "opacity-0 translate-y-10 pointer-events-none"
                }`}
            aria-label="Back to top"
        >
            <i className="ph-bold ph-caret-up text-xl"></i>
        </button>
    );
}
