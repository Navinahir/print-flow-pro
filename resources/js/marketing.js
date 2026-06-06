const THEME_STORAGE_KEY =
  document.documentElement.dataset.themeStorageKey || "xycubic-marketing-theme";
const LOCALE_STORAGE_KEY =
  document.documentElement.dataset.localeStorageKey || "xycubic-marketing-locale";
const LOCALE_COOKIE_NAME =
  document.documentElement.dataset.localeCookieName || "xycubic-marketing-locale";
const DEFAULT_LOCALE =
  document.documentElement.dataset.defaultLocale || "zh-TW";
const DEFAULT_THEME =
  document.documentElement.dataset.defaultTheme || "dark";

const menu = document.getElementById("navMenu");
const overlay = document.getElementById("overlay");
const toggle = document.getElementById("menuToggle");
const closeBtn = document.getElementById("closeMenu");
const themeToggle = document.getElementById("themeToggle");
const localeToggle = document.getElementById("localeToggle");
const localeMenu = document.getElementById("localeMenu");
const localeSwitcher = document.getElementById("localeSwitcher");

function getStoredTheme() {
  try {
    return localStorage.getItem(THEME_STORAGE_KEY);
  } catch {
    return null;
  }
}

function getStoredLocale() {
  try {
    return localStorage.getItem(LOCALE_STORAGE_KEY);
  } catch {
    return null;
  }
}

function getPreferredTheme() {
  const stored = getStoredTheme();

  if (stored === "light" || stored === "dark") {
    return stored;
  }

  return DEFAULT_THEME;
}

function applyTheme(theme) {
  const isDark = theme === "dark";
  document.documentElement.classList.toggle("dark", isDark);

  try {
    localStorage.setItem(THEME_STORAGE_KEY, theme);
  } catch {
    // Ignore storage errors (private browsing, etc.)
  }
}

function setLocaleCookie(locale) {
  const maxAge = 60 * 60 * 24 * 365;
  document.cookie = `${LOCALE_COOKIE_NAME}=${encodeURIComponent(locale)};path=/;max-age=${maxAge};SameSite=Lax`;
}

function persistLocale(locale) {
  try {
    localStorage.setItem(LOCALE_STORAGE_KEY, locale);
  } catch {
    // Ignore storage errors
  }

  setLocaleCookie(locale);
}

function initLocalePreference() {
  const stored = getStoredLocale();

  if (stored === null) {
    return;
  }

  setLocaleCookie(stored);
}

function initThemePreference() {
  const theme = getPreferredTheme();
  applyTheme(theme);
}

function toggleTheme() {
  const nextTheme = document.documentElement.classList.contains("dark")
    ? "light"
    : "dark";

  applyTheme(nextTheme);
}

function openMenu() {
  if (!menu || !overlay) {
    return;
  }

  menu.classList.remove("translate-x-full");
  menu.classList.add("translate-x-0");
  overlay.classList.remove("opacity-0", "invisible");
  toggle?.setAttribute("aria-expanded", "true");
}

function closeMenu() {
  if (!menu || !overlay) {
    return;
  }

  menu.classList.remove("translate-x-0");
  menu.classList.add("translate-x-full");
  overlay.classList.add("opacity-0", "invisible");
  toggle?.setAttribute("aria-expanded", "false");
}

function closeLocaleMenu() {
  localeMenu?.classList.add("hidden");
  localeToggle?.setAttribute("aria-expanded", "false");
}

function toggleLocaleMenu() {
  if (!localeMenu || !localeToggle) {
    return;
  }

  const isOpen = !localeMenu.classList.contains("hidden");

  if (isOpen) {
    closeLocaleMenu();
  } else {
    localeMenu.classList.remove("hidden");
    localeToggle.setAttribute("aria-expanded", "true");
  }
}

initLocalePreference();
initThemePreference();

themeToggle?.addEventListener("click", toggleTheme);

localeToggle?.addEventListener("click", (event) => {
  event.stopPropagation();
  toggleLocaleMenu();
});

document.querySelectorAll("[data-marketing-locale]").forEach((link) => {
  link.addEventListener("click", () => {
    const locale = link.getAttribute("data-marketing-locale");

    if (locale) {
      persistLocale(locale);
    }
  });
});

document.addEventListener("click", (event) => {
  if (!localeSwitcher?.contains(event.target)) {
    closeLocaleMenu();
  }
});

document.addEventListener("keydown", (event) => {
  if (event.key === "Escape") {
    closeLocaleMenu();
    closeMenu();
  }
});

toggle?.addEventListener("click", openMenu);
closeBtn?.addEventListener("click", closeMenu);
overlay?.addEventListener("click", closeMenu);
