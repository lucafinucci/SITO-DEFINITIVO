import { useEffect, useRef, useState } from "react";

export default function BackgroundCanvas() {
  const canvasRef = useRef(null);
  const [isDark, setIsDark] = useState(document.documentElement.classList.contains('dark'));

  useEffect(() => {
    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        if (mutation.attributeName === 'class') {
          setIsDark(document.documentElement.classList.contains('dark'));
        }
      });
    });

    observer.observe(document.documentElement, { attributes: true });
    return () => observer.disconnect();
  }, []);

  useEffect(() => {
    const canvas = canvasRef.current;
    if (!canvas) return;

    const ctx = canvas.getContext("2d", { alpha: true });
    let w = (canvas.width = window.innerWidth);
    let h = (canvas.height = window.innerHeight);

    const onResize = () => {
      w = canvas.width = window.innerWidth;
      h = canvas.height = window.innerHeight;
    };
    window.addEventListener("resize", onResize);

    const PARTICLES = Math.min(90, Math.floor((w * h) / 18000));
    const MAX_SPEED = 0.4;
    const LINK_DIST = Math.min(180, Math.max(110, Math.min(w, h) * 0.22));

    const rnd = (min, max) => Math.random() * (max - min) + min;

    const nodes = Array.from({ length: PARTICLES }).map(() => ({
      x: rnd(0, w),
      y: rnd(0, h),
      vx: rnd(-MAX_SPEED, MAX_SPEED),
      vy: rnd(-MAX_SPEED, MAX_SPEED),
      r: rnd(0.6, 1.8),
    }));

    let rafId;
    const gradientStroke = () => {
      const g = ctx.createLinearGradient(0, 0, w, h);
      if (isDark) {
        g.addColorStop(0, "rgba(0,224,255,0.4)");
        g.addColorStop(1, "rgba(59,130,246,0.4)");
      } else {
        g.addColorStop(0, "rgba(0,180,216,0.2)");
        g.addColorStop(1, "rgba(0,119,182,0.2)");
      }
      return g;
    };

    const draw = () => {
      ctx.clearRect(0, 0, w, h);

      if (isDark) {
        ctx.fillStyle = "rgba(7,12,22,0.75)";
        ctx.fillRect(0, 0, w, h);

        const rg = ctx.createRadialGradient(w * 0.5, h * 0.3, 0, w * 0.5, h * 0.3, Math.max(w, h) * 0.7);
        rg.addColorStop(0, "rgba(23,162,255,0.10)");
        rg.addColorStop(1, "rgba(0,0,0,0)");
        ctx.fillStyle = rg;
        ctx.fillRect(0, 0, w, h);
      } else {
        ctx.fillStyle = "rgba(253,251,247,0.85)";
        ctx.fillRect(0, 0, w, h);
      }

      ctx.globalCompositeOperation = isDark ? "lighter" : "source-over";
      for (let i = 0; i < nodes.length; i++) {
        const n = nodes[i];
        n.x += n.vx;
        n.y += n.vy;

        if (n.x < 0 || n.x > w) n.vx *= -1;
        if (n.y < 0 || n.y > h) n.vy *= -1;

        ctx.beginPath();
        ctx.arc(n.x, n.y, n.r, 0, Math.PI * 2);
        ctx.fillStyle = isDark ? "rgba(56,189,248,0.5)" : "rgba(0,180,216,0.3)";
        ctx.fill();
      }

      ctx.lineWidth = 0.7;
      ctx.strokeStyle = gradientStroke();
      for (let i = 0; i < nodes.length; i++) {
        for (let j = i + 1; j < nodes.length; j++) {
          const dx = nodes[i].x - nodes[j].x;
          const dy = nodes[i].y - nodes[j].y;
          const dist = Math.hypot(dx, dy);
          if (dist < LINK_DIST) {
            const alpha = 1 - dist / LINK_DIST;
            ctx.globalAlpha = isDark ? alpha * 0.4 : alpha * 0.2;
            ctx.beginPath();
            ctx.moveTo(nodes[i].x, nodes[i].y);
            ctx.lineTo(nodes[j].x, nodes[j].y);
            ctx.stroke();
          }
        }
      }
      ctx.globalAlpha = 1;

      rafId = requestAnimationFrame(draw);
    };

    draw();
    return () => {
      cancelAnimationFrame(rafId);
      window.removeEventListener("resize", onResize);
    };
  }, [isDark]);

  return (
    <>
      <canvas
        ref={canvasRef}
        className="fixed inset-0 -z-10 h-full w-full"
        aria-hidden="true"
      />
      <div className="pointer-events-none fixed inset-0 -z-10">
        <div className={`absolute inset-0 transition-opacity duration-700 ${isDark ? 'opacity-40' : 'opacity-10'} [background:linear-gradient(120deg,#0b1220_20%,#0a1a2b_60%,#03101f_85%)]`} />
        {isDark && <div className="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.05)_1px,transparent_1px)] bg-[length:100%_28px] mix-blend-overlay" />}
      </div>
    </>
  );
}
