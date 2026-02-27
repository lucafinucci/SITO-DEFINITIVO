import React from 'react';
import { Sun, Moon } from 'lucide-react';
import { useTheme } from '../context/ThemeContext';

export default function ThemeToggle() {
    const { theme, toggleTheme } = useTheme();

    return (
        <button
            onClick={toggleTheme}
            className="relative inline-flex h-10 w-10 items-center justify-center rounded-lg border border-border bg-muted/40 text-muted-foreground transition-all hover:border-primary/50 hover:text-primary focus:outline-none"
            aria-label="Toggle theme"
        >
            <div className="relative h-5 w-5">
                <Sun
                    className={`absolute inset-0 h-5 w-5 transition-all duration-300 ${theme === 'dark' ? 'rotate-90 scale-0 opacity-0' : 'rotate-0 scale-100 opacity-100'
                        }`}
                />
                <Moon
                    className={`absolute inset-0 h-5 w-5 transition-all duration-300 ${theme === 'dark' ? 'rotate-0 scale-100 opacity-100' : '-rotate-90 scale-0 opacity-0'
                        }`}
                />
            </div>
        </button>
    );
}
