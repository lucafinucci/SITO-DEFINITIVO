import { useEffect } from "react";

/* Reveal-on-scroll: aggiunge .in agli elementi .reveal quando entrano in viewport.
   Gestisce anche lo scroll all'ancora presente nell'URL (es. /#contatti). */
export default function useReveal() {
  useEffect(() => {
    const els = document.querySelectorAll(".reveal");
    const io = new IntersectionObserver(
      (entries) =>
        entries.forEach((e) => {
          if (e.isIntersecting) {
            e.target.classList.add("in");
            io.unobserve(e.target);
          }
        }),
      { threshold: 0.12 }
    );
    els.forEach((el) => io.observe(el));

    if (window.location.hash) {
      const id = window.location.hash.slice(1);
      setTimeout(
        () => document.getElementById(id)?.scrollIntoView({ behavior: "smooth" }),
        200
      );
    }
    return () => io.disconnect();
  }, []);
}
