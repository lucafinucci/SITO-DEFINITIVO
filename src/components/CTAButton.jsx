export default function CTAButton({ children='Richiedi una demo', variant='primary', onClick }) {
  return (
    <button className={`cta ${variant==='secondary' ? 'secondary' : ''}`} onClick={onClick}>
      {children}
    </button>
  );
}
