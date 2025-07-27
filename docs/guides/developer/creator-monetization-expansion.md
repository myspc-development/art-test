---
title: Creator Monetization Expansion – Merchandise & Tipping System
category: developer
role: developer
last_updated: 2025-07-20
status: complete
---
# Creator Monetization Expansion – Merchandise & Tipping System

This guide outlines the optional module for artists to sell merchandise and accept tips directly through ArtPulse. It expands the existing monetization docs with a simple storefront and tipping tools.

## 1. Artist Storefront Page
Each artist receives a storefront at `/artist/{username}/shop` displaying branding, a short bio and their listed products. Visitors can add items to the cart or tip the artist directly.

## 2. Product System
Artists can manage digital or physical products with the following fields:

| Field | Description |
| ----- | ----------- |
| title | Product name |
| description | Markdown or rich text |
| price | Fixed price or "Pay what you want" |
| type | `digital` or `physical` |
| inventory | Optional stock limit |
| download_link | For digital items |
| image | Product photo or preview |
| visibility | Public / Hidden / Draft |

Products are stored in the `ap_products` table. Orders are saved to `ap_orders`.

## 3. Tipping Widget
`TipJarWidget` lets supporters send quick tips. Presets for $2, $5 and $10 are provided along with a custom amount field. It can appear on artist dashboards, artwork pages and event screens.

Tips are stored in the `ap_tips` table via `POST /artist/{id}/tip`.

## 4. Checkout & Payment Integration
A simple cart interface aggregates merch purchases. Payments and tips go through Stripe Connect so creators receive payouts. Endpoints include `GET /cart` and `POST /checkout`. Email receipts are sent after purchase.

## 5. Artist Earnings Dashboard
`EarningsOverviewWidget` summarizes tips, merch sales and ticketing revenue. Artists can withdraw their balance through Stripe from this panel.

## Database Tables
```sql
ap_products(id, artist_id, title, description, price, type, inventory, download_link, visibility, created_at)
ap_orders(id, user_id, product_id, quantity, total_price, status, created_at)
ap_tips(id, artist_id, user_id, amount, message, created_at)
```

## REST Endpoints
| Method | Endpoint | Purpose |
| ------ | -------- | ------- |
| GET | `/artist/{id}/shop` | Fetch storefront |
| POST | `/artist/{id}/product` | Add/update product |
| POST | `/checkout` | Process order or tip |
| POST | `/artist/{id}/tip` | Submit tip |
| GET | `/artist/{id}/earnings` | Earnings summary |

## Components
StorefrontGrid, TipJarWidget, CartSidebar, ProductUploader and EarningsOverviewWidget compose the UI. Only artists can manage products or receive tips. Members may browse, purchase or support.

## Optional Enhancements
Discount codes, bundles, print-on-demand integration and limited-time drops can be layered on later.
