import { useRef, useEffect } from "react";
import Navbar from "./Navbar";
import Footer from "./Footer";
import { useTheme } from "../context/ThemeContext";

export default function Layout({ children }) {
    const canvasRef = useRef(null);
    const { theme } = useTheme();

    // Particle animation logic preserved for the main frame
    useEffect(() => {
        const canvas = canvasRef.current;
        if (!canvas) return;

        const ctx = canvas.getContext("2d");
        let animationFrameId;

        let width = (canvas.width = window.innerWidth);
        let height = (canvas.height = window.innerHeight);

        const particles = [];
        const particleCount = 60;

        class Particle {
            constructor() {
                this.reset();
            }
            reset() {
                this.x = Math.random() * width;
                this.y = Math.random() * height;
                this.size = Math.random() * 1.5 + 0.5;
                this.vx = (Math.random() - 0.5) * 0.4;
                this.vy = (Math.random() - 0.5) * 0.4;
                this.opacity = Math.random() * 0.5 + 0.2;
            }
            update() {
                this.x += this.vx;
                this.y += this.vy;
                if (this.x < 0 || this.x > width || this.y < 0 || this.y > height) this.reset();
            }
            draw() {
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fillStyle = theme === "dark"
                    ? `rgba(34, 211, 238, ${this.opacity})`
                    : `rgba(45, 125, 70, ${this.opacity * 0.5})`;
                ctx.fill();
            }
        }

        for (let i = 0; i < particleCount; i++) particles.push(new Particle());

        const animate = () => {
            ctx.clearRect(0, 0, width, height);
            particles.forEach((p) => {
                p.update();
                p.draw();
            });
            // Draw grid
            ctx.beginPath();
            ctx.strokeStyle = theme === "dark"
                ? "rgba(34, 211, 238, 0.03)"
                : "rgba(45, 125, 70, 0.02)";
            ctx.lineWidth = 1;
            const step = 60;
            for (let x = 0; x < width; x += step) {
                ctx.moveTo(x, 0); ctx.lineTo(x, height);
            }
            for (let y = 0; y < height; y += step) {
                ctx.moveTo(0, y); ctx.lineTo(width, y);
            }
            ctx.stroke();
            animationFrameId = requestAnimationFrame(animate);
        };

        animate();

        const handleResize = () => {
            width = canvas.width = window.innerWidth;
            height = canvas.height = window.innerHeight;
        };

        window.addEventListener("resize", handleResize);
        return () => {
            cancelAnimationFrame(animationFrameId);
            window.removeEventListener("resize", handleResize);
        };
    }, [theme]);

    return (
        <div className="relative min-h-screen text-foreground selection:bg-primary/30 antialiased">
            <Navbar />

            {/* Background canvas */}
            <canvas
                ref={canvasRef}
                className="fixed inset-0 -z-10 h-full w-full opacity-0 dark:opacity-100 transition-opacity duration-500"
                aria-hidden="true"
            />

            {/* Additional aesthetic layers */}
            <div className="pointer-events-none fixed inset-0 -z-10 dark:block hidden">
                <div className="absolute inset-0 opacity-40 [background:linear-gradient(120deg,#0b1220_20%,#0a1a2b_60%,#03101f_85%)]" />
                <div className="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.05)_1px,transparent_1px)] bg-[length:100%_28px] mix-blend-overlay" />
            </div>

            <main className="relative pt-28 sm:pt-32 lg:pt-36">
                {children}
            </main>

            <Footer />
        </div>
    );
}
