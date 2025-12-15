import { useEffect, useRef } from 'react';

export default function NeuralBackground() {
  const canvasRef = useRef(null);

  useEffect(() => {
    const canvas = canvasRef.current;
    if (!canvas) return;

    const ctx = canvas.getContext('2d', { alpha: true });
    let w = (canvas.width = window.innerWidth);
    let h = (canvas.height = window.innerHeight);

    // Handle resize
    const onResize = () => {
      w = canvas.width = window.innerWidth;
      h = canvas.height = window.innerHeight;
    };
    window.addEventListener('resize', onResize);

    // Particles
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
      g.addColorStop(0, 'rgba(0,224,255,0.85)');
      g.addColorStop(1, 'rgba(59,130,246,0.85)');
      return g;
    };

    const draw = () => {
      ctx.clearRect(0, 0, w, h);

      // subtle dark veil
      ctx.fillStyle = 'rgba(7,12,22,0.75)';
      ctx.fillRect(0, 0, w, h);

      // radial glow
      const rg = ctx.createRadialGradient(w * 0.5, h * 0.3, 0, w * 0.5, h * 0.3, Math.max(w, h) * 0.7);
      rg.addColorStop(0, 'rgba(23,162,255,0.10)');
      rg.addColorStop(1, 'rgba(0,0,0,0)');
      ctx.fillStyle = rg;
      ctx.fillRect(0, 0, w, h);

      // update & draw nodes
      ctx.globalCompositeOperation = 'lighter';
      for (let i = 0; i < nodes.length; i++) {
        const n = nodes[i];
        n.x += n.vx;
        n.y += n.vy;

        // bounce
        if (n.x < 0 || n.x > w) n.vx *= -1;
        if (n.y < 0 || n.y > h) n.vy *= -1;

        // node point
        ctx.beginPath();
        ctx.arc(n.x, n.y, n.r, 0, Math.PI * 2);
        ctx.fillStyle = 'rgba(56,189,248,0.65)';
        ctx.fill();
      }

      // links
      ctx.lineWidth = 0.7;
      ctx.strokeStyle = gradientStroke();
      for (let i = 0; i < nodes.length; i++) {
        for (let j = i + 1; j < nodes.length; j++) {
          const dx = nodes[i].x - nodes[j].x;
          const dy = nodes[i].y - nodes[j].y;
          const dist = Math.sqrt(dx * dx + dy * dy);
          if (dist < LINK_DIST) {
            const alpha = (1 - dist / LINK_DIST) * 0.4;
            ctx.globalAlpha = alpha;
            ctx.beginPath();
            ctx.moveTo(nodes[i].x, nodes[i].y);
            ctx.lineTo(nodes[j].x, nodes[j].y);
            ctx.stroke();
          }
        }
      }
      ctx.globalAlpha = 1;
      ctx.globalCompositeOperation = 'source-over';

      rafId = requestAnimationFrame(draw);
    };

    draw();

    return () => {
      cancelAnimationFrame(rafId);
      window.removeEventListener('resize', onResize);
    };
  }, []);

  return (
    <canvas
      ref={canvasRef}
      style={{
        position: 'fixed',
        top: 0,
        left: 0,
        width: '100%',
        height: '100%',
        zIndex: -1,
        pointerEvents: 'none',
      }}
    />
  );
}
