import { config } from '@fortawesome/fontawesome-svg-core';
import '@fortawesome/fontawesome-svg-core/styles.css';
import { faInstagram, faTelegram, faLinkedinIn } from '@fortawesome/free-brands-svg-icons';
import {
    faLink,
    faChevronLeft,
    faChevronRight,
    faChevronDown,
    faTag,
    faImage,
    faMagnifyingGlass,
    faUser,
    faCartShopping,
    faBars,
    faXmark,
} from '@fortawesome/free-solid-svg-icons';

// Vite bundles the CSS above, so disable runtime CSS injection (avoids SSR mismatch).
config.autoAddCss = false;

// Pass icon objects directly to <Icon> (SSR-safe, tree-shakeable; no string lookups).
export const socialIcons = {
    instagram: faInstagram,
    telegram: faTelegram,
    linkedin: faLinkedinIn,
    fallback: faLink,
};

// Shared UI icons used across storefront components.
export const uiIcons = {
    chevronLeft: faChevronLeft,
    chevronRight: faChevronRight,
    chevronDown: faChevronDown,
    tag: faTag,
    image: faImage,
    search: faMagnifyingGlass,
    user: faUser,
    cart: faCartShopping,
    menu: faBars,
    close: faXmark,
};
