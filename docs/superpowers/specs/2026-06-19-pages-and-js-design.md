# Pages & JS Enhancement Design

## Overview
Add 6 new pages (About, Pricing, Contact, Login, Sign Up, FAQ), FontAwesome icons, and JavaScript interactivity to the AutiMind project.

## New Pages

### about.html
- Hero: large heading "About AutiMind" with gradient tag
- Sections: Mission statement, features grid (4 cards), team/impact stats
- Follows existing hero pattern with section-tag style

### pricing.html  
- Hero + plan cards (3 tiers: Basic, Premium, Family)
- Each card: price, features list, CTA button
- Annual/monthly toggle (JS)

### contact.html
- Hero + 2-column layout: form left, info/social right
- Form fields: name, email, subject, message
- Contact info cards (phone, email, location) with FA icons

### login.html
- Centered card layout, no hero section
- Email + password fields, "Remember me" checkbox, submit button
- Link to signup and forgot password
- Subtle background illustration

### signup.html
- Centered card layout matching login
- Name, email, password, confirm password fields
- Role selector (Parent / Specialist / Educator)
- Link to login

### faq.html
- Hero + accordion list
- Categories filter (General, Features, Pricing, Technical)
- Each FAQ item: question (clickable) → animated answer reveal

## CSS
- All new styles in styles.css following existing naming conventions (`page-classname`)
- Responsive breakpoints at 1200px, 992px, 768px, 480px matching existing pattern

## JavaScript (app.js)
- Mobile nav toggle (hamburger menu)
- Active nav link highlighting
- Form validation (email format, required fields, password match)
- FAQ accordion (click to expand/collapse)
- Smooth scroll for anchor links
- Pricing toggle (annual/monthly)
- Scroll-triggered fade-in animations (IntersectionObserver)

## FontAwesome
- CDN: `https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css`
- Replace static img icons in nav/footer with FA where appropriate
- Use FA for social icons, form validation indicators, FAQ arrows, feature checkmarks

## Navigation Updates
Add links to all existing navbars: About, Pricing, FAQ, Contact
Reorder: Home → About → Program → Children → Parents → Specialists → Pricing → FAQ → Contact → Get Started
