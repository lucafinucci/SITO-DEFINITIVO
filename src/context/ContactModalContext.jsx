import { createContext, useContext, useState, useCallback } from "react";
import ContactModal from "@/components/ContactModal";

const ContactModalContext = createContext({ openContact: () => {}, closeContact: () => {} });

export const useContactModal = () => useContext(ContactModalContext);

export function ContactModalProvider({ children }) {
  const [open, setOpen] = useState(false);
  const [prefill, setPrefill] = useState(null); // es. { need: "Demo OmniFlow" }

  const openContact = useCallback((opts) => {
    setPrefill(opts && opts.prefill ? opts.prefill : null);
    setOpen(true);
  }, []);
  const closeContact = useCallback(() => setOpen(false), []);

  return (
    <ContactModalContext.Provider value={{ openContact, closeContact }}>
      {children}
      <ContactModal open={open} onClose={closeContact} prefill={prefill} />
    </ContactModalContext.Provider>
  );
}
