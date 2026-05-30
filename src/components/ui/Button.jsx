import React from "react";
import { cn } from "@/utils/utils";

/**
 * Button — shadcn-style API che rende le classi del design system Finch
 * (.btn / .btn-primary / .btn-ghost / .btn-lime / .btn-text definite in
 * src/styles/finch-design.css).
 *
 * Props:
 *  - variant: "primary" | "ghost" | "lime" | "text"  (default "primary")
 *  - onDark:  applica il modificatore `.on-dark` (per superfici scure)
 *  - asChild: rende il figlio (es. <Link>/<a>) ereditando le classi
 *
 * Esempi:
 *  <Button variant="primary" asChild><Link to="/...">Vai <ArrowUpRight/></Link></Button>
 *  <Button variant="ghost" onClick={...}>Esplora</Button>
 */
const VARIANTS = {
  primary: "btn btn-primary",
  ghost: "btn btn-ghost",
  lime: "btn btn-lime",
  white: "btn btn-primary", // alias usato su superfici a gradiente (CTA)
  text: "btn-text",
};

const Button = React.forwardRef(function Button(
  { variant = "primary", onDark = false, asChild = false, className, children, ...props },
  ref
) {
  const classes = cn(VARIANTS[variant] || VARIANTS.primary, onDark && "on-dark", className);

  if (asChild && React.isValidElement(children)) {
    return React.cloneElement(children, {
      ref,
      className: cn(classes, children.props.className),
      ...props,
    });
  }

  return (
    <button ref={ref} className={classes} {...props}>
      {children}
    </button>
  );
});

export default Button;
