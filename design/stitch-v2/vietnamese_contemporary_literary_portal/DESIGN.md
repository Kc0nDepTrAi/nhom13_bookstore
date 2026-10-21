---
name: Vietnamese Contemporary Literary Portal
colors:
  surface: '#f8f9ff'
  surface-dim: '#cbdbf5'
  surface-bright: '#f8f9ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#eff4ff'
  surface-container: '#e5eeff'
  surface-container-high: '#dce9ff'
  surface-container-highest: '#d3e4fe'
  on-surface: '#0b1c30'
  on-surface-variant: '#3d4947'
  inverse-surface: '#213145'
  inverse-on-surface: '#eaf1ff'
  outline: '#6d7a77'
  outline-variant: '#bcc9c6'
  surface-tint: '#006a61'
  primary: '#00685f'
  on-primary: '#ffffff'
  primary-container: '#008378'
  on-primary-container: '#f4fffc'
  inverse-primary: '#6bd8cb'
  secondary: '#006a63'
  on-secondary: '#ffffff'
  secondary-container: '#99efe5'
  on-secondary-container: '#006f67'
  tertiary: '#825100'
  on-tertiary: '#ffffff'
  tertiary-container: '#a36700'
  on-tertiary-container: '#fffbff'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#89f5e7'
  primary-fixed-dim: '#6bd8cb'
  on-primary-fixed: '#00201d'
  on-primary-fixed-variant: '#005049'
  secondary-fixed: '#9cf2e8'
  secondary-fixed-dim: '#80d5cb'
  on-secondary-fixed: '#00201d'
  on-secondary-fixed-variant: '#00504a'
  tertiary-fixed: '#ffddb8'
  tertiary-fixed-dim: '#ffb95f'
  on-tertiary-fixed: '#2a1700'
  on-tertiary-fixed-variant: '#653e00'
  background: '#f8f9ff'
  on-background: '#0b1c30'
  surface-variant: '#d3e4fe'
typography:
  display-hero:
    fontFamily: Newsreader
    fontSize: 48px
    fontWeight: '600'
    lineHeight: 56px
    letterSpacing: -0.02em
  display-hero-mobile:
    fontFamily: Newsreader
    fontSize: 32px
    fontWeight: '600'
    lineHeight: 40px
    letterSpacing: -0.01em
  headline-lg:
    fontFamily: Newsreader
    fontSize: 36px
    fontWeight: '600'
    lineHeight: 44px
    letterSpacing: -0.015em
  headline-lg-mobile:
    fontFamily: Newsreader
    fontSize: 26px
    fontWeight: '600'
    lineHeight: 34px
    letterSpacing: -0.01em
  headline-md:
    fontFamily: Newsreader
    fontSize: 24px
    fontWeight: '500'
    lineHeight: 32px
  headline-sm:
    fontFamily: Newsreader
    fontSize: 20px
    fontWeight: '500'
    lineHeight: 28px
  title-md:
    fontFamily: Be Vietnam Pro
    fontSize: 16px
    fontWeight: '600'
    lineHeight: 24px
  title-sm:
    fontFamily: Be Vietnam Pro
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 20px
  body-lg:
    fontFamily: Be Vietnam Pro
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 26px
  body-md:
    fontFamily: Be Vietnam Pro
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 22px
  body-sm:
    fontFamily: Be Vietnam Pro
    fontSize: 12px
    fontWeight: '400'
    lineHeight: 18px
  label-md:
    fontFamily: Be Vietnam Pro
    fontSize: 13px
    fontWeight: '500'
    lineHeight: 18px
  label-sm:
    fontFamily: Be Vietnam Pro
    fontSize: 11px
    fontWeight: '500'
    lineHeight: 16px
    letterSpacing: 0.02em
  price-lg:
    fontFamily: Be Vietnam Pro
    fontSize: 22px
    fontWeight: '700'
    lineHeight: 28px
  price-md:
    fontFamily: Be Vietnam Pro
    fontSize: 16px
    fontWeight: '600'
    lineHeight: 22px
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  gutter: 1.5rem
  gutter-mobile: 0.75rem
  margin: 2rem
  margin-mobile: 1rem
  space-xs: 0.25rem
  space-sm: 0.5rem
  space-md: 1rem
  space-lg: 1.5rem
  space-xl: 2.5rem
  space-2xl: 4rem
---

## Brand & Style

### Brand Personality & Tone
This design system crafts an upscale, focused, and tranquil digital sanctuary for Vietnamese bibliophiles, students, and collectors. The interface rejects visual clutter and neon discount banners typical of mass-market e-commerce, embracing instead the stillness and intellectual serenity of an architectural concept bookstore. 

The aesthetic is tailored specifically for literary discovery and meticulous catalog browsing:
- **Refined & Cerebral:** Emphasizes content, typography, and book cover artistry rather than gimmicky merchandising badges.
- **Calm & Tactile:** Mimics high-grade ivory unprinted paper (`#FBFBFA`) anchored with deep ink-slate typography (`#0F172A`) and deep botanical teal tones (`#0D9488` / `#0F766E`).
- **Cultured & Trustworthy:** Conveys rigor, prompt order handling, and dependable curation across storefront browsing and operational admin flows.

### Design Movement: Warm Editorial Modernism
The visual language fuses modern Scandinavian editorial layouts with tactile paper-like warmth. It relies on clean, low-contrast structural dividers, micro-borders with subtle ambient tinting, generous margins, and deliberate typographic rhythm optimized for diacritics in Vietnamese text.

## Colors

### Palette Philosophy
The palette simulates the natural contrast of physical publishing: crisp book papers, archival ink, and tranquil bookshop accents. High-saturation reds and yellows are strictly isolated to transactional order states or alerts.

### Color Tiers & Usages
- **Primary (`#0D9488`) & Deep Primary (`#0F766E`):** Represents growth, curiosity, and brand touchpoints. Used for primary CTAs ("Thêm vào giỏ", "Mua ngay"), active navigation indicators, key admin progress highlights, and focused interactive states.
- **Ink & Typography Neutral (`#0F172A`):** Deep ink charcoal replaces pure black for headlines, book titles, and body content to reduce optical strain on ivory backgrounds.
- **Secondary Slate Neutral (`#64748B`):** Reserved for author attribution, metadata, category tags, timestamps, and input field helper copy.
- **Surface Canvas (`#FBFBFA`):** An ivory/unbleached linen surface applied to the global application body.
- **Elevated Canvas (`#FFFFFF`):** Pure white reserved for book product cards, modal surfaces, floating sidebars, and input container surfaces.
- **Dividers & Subtle Borders (`#F1F5F9` / `#E2E8F0`):** Ultra-soft delineations that structure grids without harsh visual cuts.

### Order Fulfillment Status Palette
Order lifecycle states require standardized color mappings across customer accounts and administrative dashboards:
- **Chờ xử lý (Pending Confirmation):** Background `#FEF3C7`, Text/Icon `#B45309`, Border `#FDE68A`.
- **Đang xử lý (Processing / Packing):** Background `#E0F2FE`, Text/Icon `#0369A1`, Border `#BAE6FD`.
- **Đang giao (In Transit):** Background `#F0FDFA`, Text/Icon `#0F766E`, Border `#99F6E4`.
- **Đã hoàn thành (Delivered / Completed):** Background `#ECFDF5`, Text/Icon `#047857`, Border `#A7F3D0`.
- **Đã hủy (Cancelled / Returned):** Background `#FEE2E2`, Text/Icon `#B91C1C`, Border `#FECACA`.

### Currency & Price Styling Standard
- Formatted with explicit dot thousand separators and the currency sign `₫` (e.g., `185.000 ₫`).
- Current selling price: Rendered in `#0F172A` (weight 600) or `#0D9488`.
- Original retail price (strike-through): Rendered in `#94A3B8` (weight 400, `line-through`).
- Percentage discount chip: Soft pill `#FEF2F2` text `#DC2626`.

## Typography

### Typographic Pairing
- **Editorial Voice (Headlines & Literary Titles):** `Newsreader`. A classical, intellectual serif with optical sizing that gives titles a literary feel without sacrificing screen legibility.
- **Operational & Functional Voice (Body, UI, Metadata, Prices):** `Be Vietnam Pro`. Designed natively for the Vietnamese language with meticulous diacritical mark placement, balanced tone marks (`hỏi`, `ngã`, `nặng`), and high legibility across dense administrative tables and book blurbs.

### Vietnamese Typesetting Rules
- Maintain line heights of at least 1.45–1.6× on body text to accommodate stacked tone marks (`ê`, `ơ`, `ư` with diacritics) without vertical collision.
- Avoid aggressive uppercase letter-spacing on serif titles that include multiple accent marks.
- Book prices must always render in tabular numeral variants (`font-feature-settings: "tnum"`) to ensure alignment in checkout summaries and administrative order tables.

## Layout & Spacing

### Layout Model
The layout adheres to a structured 12-column grid system bounded inside a maximum content wrapper of `1320px` for the public storefront, and a responsive fluid layout with a persistent `260px` collapsed/expandable rail for the admin console.

### Breakpoints & Adaptive Rhythm
- **Mobile (< 768px):** 4 columns, `margin-mobile` (16px), `gutter-mobile` (12px). Book catalog displays in a tight 2-column grid. Filter sidebars collapse into a slide-up bottom sheet.
- **Tablet (768px – 1023px):** 8 columns, 24px margins, 16px gutters. Catalog reflows to 3 columns.
- **Desktop (1024px+):** 12 columns, 32px margins, 24px gutters. Standard catalog renders 4 to 5 cards per row, reserving 3 columns for persistent faceted filtering (Thể loại, Tác giả, Nhà xuất bản, Mức giá).

### Spacing Principles
- Component gaps default to `space-md` (16px) for predictable visual cadence.
- Sections on the storefront alternate between dense listing grids and breathing room (`space-2xl` / 64px) around curated collections.
- Admin dashboard pages utilize compact vertical rhythms with dense tables (`space-xs` to `space-sm` row padding) to optimize screen real estate.

## Elevation & Depth

### Depth Model: Atmospheric Soft Shadow & Paper Layering
Rather than heavy dropshadows or stark outlines, depth relies on micro-borders coupled with diffused ambient tints mimicking light falling across physical pages:

- **Surface Level 0 (Background Canvas):** `#FBFBFA`. Completely flat backdrop.
- **Surface Level 1 (Book Cards, Content Panes, Table Rows):** `#FFFFFF` paired with an ultra-thin border `1px solid #F1F5F9` and an ambient shadow `0 1px 3px rgba(15, 23, 42, 0.04), 0 1px 2px rgba(15, 23, 42, 0.02)`.
- **Surface Level 2 (Hovered Book Cards, Dropdown Menus, Popovers):** `#FFFFFF` with `0 10px 15px -3px rgba(15, 23, 42, 0.05), 0 4px 6px -4px rgba(15, 23, 42, 0.03)` and border `1px solid #E2E8F0`. Hovering a book card slightly translates it upward (`transform: translateY(-3px)`) with crisp book cover edge enhancement.
- **Surface Level 3 (Dialogs, Modals, Quick View Book Detail):** `#FFFFFF` with `0 20px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.03)` accompanied by a gentle scrim overlay (`#0F172A` at 40% opacity with 2px backdrop blur).

## Shapes

### Shape Language: Soft Editorial (Level 1)
To complement literary physical media, elements feature restrained, subtle curvature that preserves rectangular structure without harshness.

- **Standard Elements (Buttons, Inputs, Table Cells, Card Containers):** `0.25rem` (4px). Clean, precise, and professional.
- **Mid-sized Enclosures (Modals, Feature Banners, Hero Panels):** `0.5rem` (8px).
- **Status Badges, Category Chips, Counter Pills:** Full radius pill (`9999px`) to distinguish interactive filters and status indicators from rectangular book forms.
- **Book Cover Media:** `3px` radius with a deliberate `1px inside inset shadow` simulating a physical book's spine crease and clean paper trim.

## Components

### Buttons
- **Primary Button ("Thêm vào giỏ", "Thanh toán"):** Solid `#0D9488` background, `#FFFFFF` text, `Be Vietnam Pro` 14px weight 600, padding `10px 20px`, roundedness `4px`. Hover: `#0F766E`. Active: Scale `0.99`.
- **Secondary / Literary Outline ("Đọc thử", "Xem chi tiết"):** Transparent background, `1px solid #CBD5E1`, text `#0F172A`. Hover: `#F8FAFC` background, border `#94A3B8`.
- **Ghost Button:** Text `#64748B`, hover background `#F1F5F9`, hover text `#0F172A`.
- **Admin Action Buttons:** Compact padding `6px 12px`, 13px label size.

### Book Product Card (Storefront)
- **Container:** Pure `#FFFFFF` surface, `1px solid #F1F5F9`, padding `16px`, subtle shadow.
- **Book Cover Presentation:** Standard 2:3 or 1:1.45 aspect ratio container with soft gray placeholder skeleton. Spine shadow effect applied via left-aligned vertical gradient `linear-gradient(to right, rgba(0,0,0,0.12) 0%, rgba(0,0,0,0.03) 3%, transparent 8%)`.
- **Content Hierarchy:**
  1. Author label: `body-sm` (`#64748B`), uppercase tracking.
  2. Book title: `Newsreader` 16px, weight 600, line-clamp 2 lines, `#0F172A`.
  3. Star rating with review count: Solid miniature stars `#F59E0B`, count `#94A3B8`.
  4. Pricing row: `price-md` in `#0D9488` or `#0F172A`, strikethrough retail price adjacent.
  5. Quick Add interaction: Floating subtle shopping bag icon or button sliding up on card hover.

### Form Inputs & Search Bar
- **Global Search ("Tìm kiếm tựa sách, tác giả, ISBN..."):** Height `44px`, background `#FFFFFF`, border `1px solid #E2E8F0`, icon prefix in `#64748B`. Focus state: border `#0D9488`, outer glow `0 0 0 3px rgba(13, 148, 136, 0.12)`.
- **Standard Admin & Checkout Fields:** `title-sm` field label in `#0F172A` with mandatory red asterisk. Inner input padding `10px 14px`. Subtle transition on focus.

### Badges & Order Status Pills
- **Geometry:** Fully rounded (`rounded-full`), padding `3px 10px`, font size `12px`, weight 500.
- **Visuals:** Follow the defined color pairings (e.g., "Đang giao": `#F0FDFA` background, `#0F766E` text, `#99F6E4` border). Always accompanied by a 6px status dot icon to reinforce accessibility for color-blind users.

### Admin Data Table
- **Header:** Background `#F8FAFC`, border bottom `1px solid #E2E8F0`, labels `12px` uppercase tracking `#64748B`.
- **Rows:** White background `#FFFFFF`, alternating hover state `#FBFBFA`, row height `56px`, vertical borderless styling with `1px solid #F1F5F9` bottom divider.
- **Order ID:** Monospace font styling (`Be Vietnam Pro` tabular or code-face), bold `#0F172A`.
- **Actions Column:** Quick actions group (Xem chi tiết, Cập nhật trạng thái, In hóa đơn) using subtle ghost icons.

### Quantity Selector (Cart & Checkout)
- Segmented counter: `36px` height, `#FFFFFF` container with `1px solid #E2E8F0` border. Minus and plus glyphs in `#64748B` with central numeric text display in `#0F172A` (weight 600).