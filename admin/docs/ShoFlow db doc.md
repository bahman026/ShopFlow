**Common Columns**

 Here are common or frequently used columns.

* `id` column is used to store the record ID.  
* `created_at` column is used to store the record creation time.  
* `updated_at` column is used to store the last update time of the record.  
* `deleted_at` column is used to store the record deletion time.

# **Addresses** 

Used to store user addresses.

* `user_id` refers to the user who owns the address.  
* `city_id` selects the city code and is mandatory.  
* `name` specifies the address name, such as home, office, or any user-defined name.  
* `phone` specifies the phone number associated with the address.  
* `postal_code` stores the address postal code.  
* `address` specifies the user's address.  
* `prime` indicates if this is the user's default address.  
* `deleted_at` indicates if this address has been deleted. Addresses are not physically deleted from the database; updated addresses create new records.  
* `description` stores address-related descriptions.  
* `latitude` and `longitude` specify the geographical coordinates.  
* 


  # Ancestors

  Used to categorize and organize attribute groups into meaningful categories.  
* name specifies the attribute category name, such as Technical Specifications.  
* order specifies the display order of the attribute categories.

# 

# Attribute\_Group

#### **Purpose**

The `attribute_groups` table is used to define groups of attributes that categorize specific characteristics of products.

#### **Columns and Description**

* **`id`**: (Primary Key) Unique identifier for the attribute group.  
* **`ancestor_id`**: Refers to the parent category this group belongs to, allowing for hierarchical grouping of attributes.  
* **`name`**: The name of the attribute group, specifying the category of attributes (e.g., "Color", "RAM", "Material").  
* **`label`**: Display name shown in the admin panel for operators (e.g., "Available Colors").  
* **`order`**: Determines the display order of the attribute groups for easier organization.

# **Attribute\_Group\_Category** 

The **`attribute_group_category`** table (singular) manages the relationship between attribute groups and categories. It defines whether an attribute group can be used as a filter in a category and whether it is mandatory for that category.

* `attribute_group_id` indicates the current record's attribute group.  
* `category_id` indicates the current record's category.  
* `as_filter` Indicates if this attribute group can be used as a filter in the specified category (1 \= Yes, 0 \= No). Boolean, defaults to false.  
* `required` Indicates if the attribute group is mandatory for products in the specified category (1 \= Yes, 0 \= No). Boolean, defaults to false.

# Attributes

 Used to store attributes.

* `attribute_group_id` refers to the attribute group. Foreign key to `attribute_groups`; deleting a group cascades and deletes its attributes.  
* `color` is used for filters with colors.  
* `value` Stores the value or name of the attribute (e.g., "8GB RAM," "Dual SIM").

# product\_attribute

 Used to store the relationship between filters and products.

* product\_id refers to the id column in the products table. Foreign key; cascades on product delete.  
* attribute\_id refers to the id column in the attributes table. Foreign key; cascades on attribute delete.  
* is\_highlight indicates if the attribute should be highlighted for the product (boolean, defaults to false).  
* The pair `(product_id, attribute_id)` is unique, so a product cannot link the same attribute twice. Carries `created_at` / `updated_at`.

# Brands

 Used to store brands.

* `heading` specifies the brand name, such as Samsung.  
* `slug` specifies the brand slug.  
* `content` stores the brand page content submitted via the editor.  
* `title` specifies the page title.  
* `description` stores the meta description of the page.  
* `no_index` indicates if the page should be noindexed on the frontend.  
* `canonical` specifies the canonical link to prevent cannibalization with product or category pages.  
* `image_id` refers to the `id` column in the `images` table.  
* `status` stores the publication status of the brands, with values 10 for deleted, 20 for published, and 30 for draft.

# **Banners** 

Used to store banners.

* `position` specifies the advertisement location, which is an arbitrary name to retrieve the corresponding record from the database.  
* `heading` specifies the banner item title or the alt text of the image.  
* `url` specifies the item link, which redirects when the image or title is clicked.  
* `sort` specifies the item order.  
* `status` stores the publication status of the banner, with values 10 for deleted, 20 for published, and 30 for draft.

Images are attached through the polymorphic `images` table (`imageable_type` / `imageable_id`), so a banner can have one or more images instead of fixed `image_id` columns. The featured image is the one with `is_featured` set to true.

# Brand\_Category

 Used to create a page for each category and brand combination. Each record in this table represents a page. This table has no `id` but uses a composite primary key combining `brand_id` and `category_id`.

* `brand_id` specifies the brand for each record.  
* `category_id` specifies the category for each record.  
* `content` stores the page content.  
* `title` stores the `<title>` tag value.  
* `description` stores the meta description value.  
* `no_index` specifies if the page has a noindex meta tag.  
* `canonical` prevents cannibalization by specifying the link of the page to which the current page's rank will be transferred.

# **Cards** 

This table stores cards.

* `prime` specifies the card priority.  
* `card_number` specifies the card number.  
* `account_number` stores the account number.  
* `iban_number` stores the IBAN number.  
* `deposit_id` stores the deposit ID.  
* `bank_name` stores the bank name.  
* `account_owner` stores the account owner's name.  
* `max_amount` displays the maximum amount for using this card, default is zero. If zero, it does not apply.  
* `min_amount` displays the minimum amount for using this card, default is zero. If zero, it does not apply.  
* `credit`  
* `hidden`  
* `referral_user_id` indicates which user referred this card. When two cards have the same `referral_user_id`, the `prime` value shows the priority of one of the cards.  
* `type` specifies the card type.

# Carts

 Specifies the shopping cart of users. Each row is one cart line: a single variety with a quantity, belonging to either a logged-in user or a guest session.

* `user_id` specifies the user who owns the cart line. Nullable foreign key to `users`; cascades on user delete.  
* `session_id` specifies the session for users who have not logged in. If a user logs in after selecting products, all items with this session move to the `user_id` column. Therefore, `session_id` and `user_id` cannot both have values simultaneously.  
* `variety_id` specifies the variety placed in the cart. Foreign key to `varieties`; cascades on variety delete.  
* `count` specifies the quantity of the variety, default `1`.

Inventory note: a cart never changes `varieties.inventory`. Stock is decremented only on successful payment. See `ORDER.md`.

# Categories

 Used to store shop categories.

* `heading` specifies the category title.  
* `slug` specifies the category slug.  
* `content` stores the category content, edited and inserted via an editor. It can be displayed partly or fully on the frontend, helping SEO. The text can appear above or below the category products depending on the design.  
* `title` specifies the category page title for SEO.  
* `description` specifies the category description.  
* `no_index` is a boolean. If true, the category should have a noindex meta tag.  
* `canonical` prevents cannibalization by specifying the canonical link.  
* `parent_id` specifies the parent category for nested structures.  
* `status` specifies the category status.

# Category\_Partner

 Specifies the categories where users are partners.

* `category_id` specifies the related category.  
* `partner_id` specifies the related partner, referring to the `users` table.

# **Category\_Coupon** 

Scopes a coupon so it applies only to products in specific categories.

* `category_id` specifies the category the coupon is allowed for. Foreign key; cascades on category delete.  
* `coupon_id` specifies the coupon this scope belongs to. Foreign key; cascades on coupon delete.  
* The pair `(category_id, coupon_id)` is unique.

# cities

* Used to store cities.  
* `name`: Specifies the name of the city.  
* `province_id`: Specifies the province to which the city belongs.  
* `shipping_line_id`: Specifies the shipping line.

# coupons

Stores discount coupons. Unlike discounts, a coupon is applied manually: the customer enters its `code` at checkout. A coupon can be limited by a minimum cart amount, capped by a maximum discount, restricted in how many times it is used, scoped to specific products, varieties, or categories (through the `coupon_product`, `coupon_variety`, and `category_coupon` tables), and can grant free shipping. When an order uses a coupon, its id and resulting amount fill `orders.coupon_id` and `orders.coupon_discount`.

* `name`: Specifies the name of the coupon.  
* `code`: The code the customer enters to apply the coupon.  
* `amount`: The discount value, read as a percentage or a fixed amount depending on `is_percent`.  
* `min_price`: The minimum purchase amount required before the coupon applies.  
* `max_discount`: The maximum discount amount the coupon can give.  
* `total_used`: How many times this coupon has already been used.  
* `total_uses`: How many times this coupon is allowed to be used in total.  
* `user_id`: Limits usage to a specific user. Nullable foreign key to `users`; set to null when the user is deleted.  
* `user_creator_id`: The admin user who created the coupon. Nullable foreign key to `users`.
* `status`: Has states "active" (default, usable), "canceled", "used", and "under review".
* `is_percent`: Indicates if the discount is a percentage or a fixed amount.  
* `shipping`: Indicates if this coupon includes free shipping (applies only to free shipping, not to the price).  
* `is_for`: Whether the coupon is usable by everyone, or only by users, or only by partners.  
* `started_at`: When the coupon becomes usable.  
* `expired_at`: When the coupon can no longer be used.

# coupon\_product

* Scopes a coupon so it can only be applied to certain products.  
* `coupon_id`: The coupon this scope belongs to. Foreign key; cascades on coupon delete.  
* `product_id`: A product the coupon is allowed for. Foreign key; cascades on product delete.  
* The pair `(coupon_id, product_id)` is unique.

# coupon\_variety

* Scopes a coupon so it applies to certain varieties only, useful for sellers who want a coupon for some of their varieties.  
* `coupon_id`: The coupon this scope belongs to. Foreign key; cascades on coupon delete.  
* `variety_id`: A variety the coupon is allowed for. Foreign key; cascades on variety delete.  
* The pair `(coupon_id, variety_id)` is unique.

# discounts

Stores automatic special prices for product varieties. Unlike coupons, no code is entered: the discount is applied by itself whenever its conditions (variety, time window, quantity, audience, and remaining limits) are met. Each row belongs to one variety, so different varieties of the same product can have different discounts. The resulting discount amount is what later fills `orders.discount` and `order_varieties.discount`.

* `variety_id`: Specifies the variety the discount applies to. Foreign key to `varieties`.  
* `quantity`: Applies this price only if at least this number of the variety is purchased, useful for bulk purchase levels.  
* `priority`: When several discounts could apply, this decides which one wins (the order of application).  
* `is_percent`: Specifies if the discount is a percentage or a fixed amount.  
* `amount`: The discount value, read as a percentage or a fixed amount depending on `is_percent`.  
* `started_at`: When the discount becomes active.  
* `ended_at`: When the discount stops being active.  
* `sold`: How many units have already been sold under this discount.  
* `max_sell`: Caps the total number of sales; once `sold` reaches it, the discount is removed.  
* `max_sell_by_user`: Caps the number of sales per user; once a user reaches it, the discount no longer applies for that user.  
* `is_for`: Whether the discount is for everyone, or only for users, or only for partners.  

In short: discounts are automatic, per-variety, condition-based price rules. They are separate from coupons, which require a code.

# email\_histories

* `user_id`: Indicates which user the email belongs to.  
* `email`: Stores the email that was changed.  
* `changer_id`: Indicates who deleted or replaced this email.

# failed\_jobs

* `uuid`: (explanation to be added).  
* `connection`: (explanation to be added).  
* `queue`: (explanation to be added).  
* `payload`: (explanation to be added).  
* `exception`: (explanation to be added).  
* `failed_at`: (explanation to be added).

# faqs

* Stores frequently asked questions shown on the storefront.
* `heading`: The question text shown to the visitor.
* `content`: The answer to the question.
* `order`: Display order — lower numbers appear first.
* `position`: Placement context. Null = shown on the main FAQ page. Any value (e.g. `"homepage"`, `"products"`) shows the FAQ in that specific section of the site.

# transactions

* This table is used to store payment transactions.  
* `user_id`: Indicates which user the transaction belongs to.  
* `order_id`: Indicates which order the transaction belongs to.  
* `ref_id`: (Received from the bank gateway).  
* `amount`: Specifies the payment amount.  
* `accounting_id`: Indicates the related accounting transaction.  
* `port`: Specifies the gateway provider, currently including Mellat, Parsian, and Zarinpal gateways. The numerical value for Mellat is 10, for Parsian is 20, and for Zarinpal is 30\. For adding, modifying, or deleting gateways and their corresponding port values, check the file `ts/Utilities/Status/Interfaces/GatewayInterface.php`.  
* `transaction_id`: Specifies the transaction ID.  
* `status`: Indicates the payment status.  
* `ip`: Specifies the user's IP address.  
* `description`: Provides payment-related descriptions.  
* `paid_at`: Stores the time of successful payment.  
* `result_code`: Stores the bank transaction response code.  
* `result_message`: Stores the bank transaction response message.

# gateways

* Stores payment gateways.  
* `id`  
* `key`: Stores the gateway key.  
* `name`: Stores the gateway name.  
* `for`: Indicates who the gateway is for.  
* `username`  
* `password`  
* `gate_id`: Stores the gateway token or ID.  
* `active`: Indicates if the gateway is active or not.  
* `priority`: Stores the gateway priority.  
* `image_id`  
* `coding`  
* `created_at`  
* `updated_at`

# guarantees

* Stores guarantees.  
* `order_id`: Specifies which order the guarantee belongs to.  
* `user_id`: Specifies the user to whom the guarantee belongs.  
* `gather_id`: Specifies the gatherer, from the users table.  
* `courier_id`: Specifies the courier who is the recipient.  
* `pack_count`: Specifies the number of boxes.  
* `status`: Specifies the guarantee status.

# guarantee\_items

* Stores items related to guarantees.  
* `variety_id`: Specifies which variety it belongs to.  
* `user_id`: Specifies which user it belongs to.  
* `address_id`: Specifies the guarantee item address.  
* `recipient_id`: Specifies the recipient.  
* `recipient_description`: Provides the recipient description.  
* `receipted_at`: Specifies the receipt date.  
* `guarantee_id`: Specifies which guarantee it belongs to.  
* `product_name`: Stores the product name.  
* `guarantee_list_id`: Specifies the guarantee name.  
* `problem_description`: Describes the reason for sending for a guarantee.  
* `entered_at`: Specifies the entry date.  
* `send_courier_id`: Specifies the courier ID for sending to the guarantee.  
* `status`: Specifies the various guarantee statuses.  
* `repair_cost`: Specifies the repair cost.  
* `creator_id`: Specifies the creator's ID.  
* `guarantee_type`: Specifies the type of guarantee: 1-Return, 2-Repair.  
* `request_send_type`: Specifies how the user will send the items: 1-ShopFlow Fleet, 2-Courier, 3-Tipax, 4-Post, 5-In-person.  
* `has_guarantee`: Indicates if the product has a guarantee or not (0-No, 1-Yes, 2-Test period).  
* `source`: Specifies the guarantee entry source (1-Site, 2-App, 3-In-person).  
* `send_description`: Provides the guarantee send description.  
* `receive_description`: Provides the guarantee receive description.  
* `sent_at`: Specifies the item send time.  
* `received_at`: Specifies the item receive time from the guarantee provider.  
* `confirm_code`: Specifies the confirmation code for the repaired item, issued after the invoice is sent.  
* `factor_number`: Specifies the invoice number for repaired items.

# guarantee\_item\_images

* Stores images related to guarantee items.  
* `guarantee_item_id`: Refers to the corresponding record from the guarantee\_items table.  
* `image_id`: Refers to the corresponding record from the images table.


# guarantee\_item\_logs

* Stores logs for the guarantee\_items table.  
* `guarantee_item_id`: Specifies which guarantee item this log belongs to.  
* `user_id`: Specifies which user this guarantee log belongs to.  
* `status`: (Explanation to be added).  
* `started_at`: (Explanation to be added).  
* `ended_at`: (Explanation to be added).

# guarantee\_logs

* Stores guarantee logs.  
* `guarantee_id`: Specifies which guarantee this log belongs to.  
* `user_id`: Specifies which user this guarantee log belongs to.  
* `status`: (Explanation to be added).  
* `started_at`: (Explanation to be added).  
* `ended_at`: (Explanation to be added).

# guarantee\_names

* Stores the names of guarantee providers.  
* `id`  
* `name`: Specifies the name of the guarantee provider.  
* `slug`  
* `self_receive`: Indicates whether the company collects the guarantees themselves or not.  
* `is_public`: Indicates whether this guarantee is shown to users on the website or not.

# helps

* `heading`: Specifies the help title.  
* `label`: Specifies the label placement on the page.  
* `content`: Displays the help content.  
* `position`: Specifies which section the help is for (admin, sellers, etc.).

# holidays

* Stores holidays when no product delivery is made.  
* `day`: Stores the holiday date.  
* `content`: Provides a description or the reason for the holiday or a message from the admin.

# images

* Stores images.  
* `path`: Stores the relative path of the images, the absolute path can be specified using the `static_asset` function relative to the CDN.  
* `imageable_type`: used for polymorphic relation — Attributes, Brand, Categories, Products, Banners, Slides, MenuItems.  
* `imageable_id`: used for polymorphic relation.  
* `order`: Display order of the image within its parent (default 0).  
* `is_featured`: Marks the primary/featured image for the parent record (default false). Only one image per parent should have this set to true.  
* `alt_text`: Optional alt text for accessibility and SEO.  
* `created_at` / `updated_at`: Record timestamps.

# jobs

* `queue`: Column  
* `payload`: Column  
* `attempts`: Column  
* `reserved_at`: Column  
* `available_at`: Column  
* `created_at`: Column

# menus

* Contains various menus in the header, footer, etc., that will be cached by the application logic.  
* `name`: Human-readable label for the menu, e.g. "Header" or "Footer Column 1".  
* `position`: The key used by the frontend to fetch this menu, e.g. "header" or "footer-column-1". Unique.  
* `status`: Boolean. When false (0), the menu is hidden on the frontend. Default true.  
* Deleting a menu cascades to all its menu items (and their images and child items).

# menu\_items

* Stores menu items.  
* `menu_id`: Specifies which menu this record belongs to. Foreign key; cascades on menu delete.  
* `parent_id`: Nullable self-referential FK to `menu_items`. Used to create one level of nested menus. Set to null when the parent is deleted.  
* `name`: The link text shown to the visitor, e.g. "About Us".  
* `url`: The URL this item links to. Nullable. Use a relative path for internal pages or a full URL for external links.  
* `label`: Optional secondary badge or tag shown beside the item name, e.g. "New". Nullable.  
* `order`: Display order within the menu or under the parent. Default 0. Lower values appear first.  
* Image is stored via the polymorphic `images` table (`imageable_type = MenuItem`) using a `MorphOne` relationship — no `image_id` column. Deleting an item cascades to its image and children.

# mobile\_histories

* `user_id`: Indicates which user this mobile number belongs to.  
* `mobile`: Stores the mobile number that has been changed.  
* `changer_id`: Indicates who deleted or replaced this mobile number.

# mobile\_password\_resets

* Resetting the password via mobile. The mechanism works similarly to password recovery via email, but instead of sending an email, an SMS containing the password recovery code is sent to the user's mobile number. The user can set their new password by entering this code on the current page.  
* `mobile`: Stores the user's mobile number.  
* `token`: Stores the user's token to verify the received token code.  
* `used`: Is a boolean, defaulting to 0\. If the user uses this code to reset their password, it becomes expired.  
* `updated_at`: Stores the current time when the password is reset after correctly entering the code and changing used.

# orders

* Used to store orders.  
* `user_id`: Indicates which user the order belongs to.  
* `coupon_id`: Stores the coupon ID if the order used a coupon; otherwise, it is null.  
* `coupon_discount`: Specifies the discount amount applied through the coupon.  
* `discount`: Specifies the discount amount applied through the discounts table.  
* `shipping_cost`: Stores the shipping cost; if it is zero or has a value, it does not affect the discount column.  
* `total_products_price`: Stores the total amount for the products in the order.  
* `tax`: Stores the total tax amount.  
* `total_price`: Specifies the total amount after applying tax and shipping cost.  
* `for_partner`: Indicates whether the order is for a partner or a customer.  
* `content`: Stores order-related descriptions.  
* `src`: Specifies the source of the order registration: 1 means PWA, 2 means WEB, 3 means APP, and 4 means old.  
* `version`: Specifies the version of the source that sent the order registration request.  
* `bijack_image_id`: Is the postal receipt (CRM) linked to the images table.  
* `bijack_description`: Provides the postal receipt description (CRM).  
* `accounting_id`: Stores the invoice, which can be null.  
* `confirmed_id`: Accounting confirmation.  
* `confirmed_at`: Accounting confirmation date.  
* `confirm_description`: Accounting confirmation description.  
* `collector_id`: Collector.  
* `collected_at`: Collection date.  
* `collector_reminded_at`: Collector reminder date.  
* `collector_description`: Collection-related description.  
* `notifier_id`: Customer notification.  
* `notified_at`: Customer notification date.  
* `shipping_line_id`: Specifies which shipping line.  
* `shipping_method_id`: Specifies which shipping method.  
* `send_description`: Provides the shipping description.  
* `receive_from` and `receive_to`: Specify the time range the user chose for delivery (e.g., from 9 to 12 or from 15 to 18).

Implementation notes:

* `status`: Added column (not in the original doc). `OrderStatusEnum`: `PENDING=10` (default), `PAID=20`, `PROCESSING=30`, `SHIPPED=40`, `DELIVERED=50`, `CANCELED=60`, `RETURNED=70`. Stock is decremented only when an order is paid (see `ORDER.md`).
* `src`: Implemented as `OrderSrcEnum` (`PWA=1`, `WEB=2` default, `APP=3`, `OLD=4`).
* `user_id`: Nullable FK to `users`, `nullOnDelete` so orders survive user deletion.
* `confirmed_id`, `collector_id`, `notifier_id`: Nullable FKs to `users` (`nullOnDelete`).
* `accounting_id`, `bijack_image_id`: Plain nullable columns with no FK constraint, because the accounting table is not built yet and images are stored polymorphically elsewhere.
* Money columns (`coupon_discount`, `discount`, `shipping_cost`, `total_products_price`, `tax`, `total_price`): `decimal(12,2)`, default `0`.
* No `seller_id` (single-vendor). The seller-centric `sub_orders` / `sub_order_logs` tables are intentionally not implemented.
* Only the `orders` table is implemented so far; `order_varieties` and the other order_* tables are not built yet.

# order\_call\_logs

* Stores call logs to the customer.  
* `order_id`: Specifies which order this log belongs to.  
* `caller_id`: Specifies the caller.  
* `type`: Three types: 1- Accounting, 2- Collection, 3- Customer call.  
* `content`: Provides call descriptions.

# order\_logs

* Stores order logs.  
* `order_id`: Column  
* `user_id`: Column  
* `seller_id`: Column  
* `status`: Column  
* `started_at`: Column  
* `ended_at`: Column

# order\_shippings

* Used to store order shipments.  
* `order_id`: Specifies which order.  
* `checker_id`: Specifies the checker.  
* `pack_count`: Specifies the number of packages.  
* `pack_user`: Specifies the packer.  
* `checked_at`: Check date.  
* `shipped_at`: Shipment date.  
* `sender_id`: Specifies the sender.  
* `courier_id`: Specifies the courier.  
* `courier_received_at`: Specifies the date received by the courier.  
* `courier_delivered_at`: Specifies the delivery date.  
* `confirm_code`: Order confirmation code.  
* `cheque_is_require`: Whether a cheque is required.  
* `courier2_id`: Specifies the courier for shipping.  
* `description`: Provides the order check description.  
* `payment_type`: Specifies the payment type: cash, POS, online.

# order\_varieties

* Used to store product varieties in orders.  
* `order_id`: Specifies which order this record belongs to.  
* `product_id`: Specifies which product this record belongs to.  
* `variety_id`: Specifies which variety this record belongs to.  
* `sub_order_id`: Specifies which sub-order it belongs to.  
* `quantity`: Specifies the quantity.  
* `gather_quantity`: Specifies the gathered quantity.  
* `invoice_quantity`: Specifies the invoiced quantity, not applicable to sellers.  
* `price`: Specifies the product price.  
* `discount`: Specifies the discount amount applied through the discounts table.  
* `coupon_discount`: Specifies the discount amount applied through the coupon.  
* `final_price`: Specifies the price multiplied by quantity.

# pages

* Used to create pages.  
* `heading`: Specifies the page title.  
* `slug`: Specifies the slug of each page.  
* `title`: Stores the page title.  
* `description`: Specifies the meta description of the page.  
* `no_index`: If true or 1, the page will have a noindex meta tag.  
* `canonical`: Used to prevent canonicalization issues and contains the link to the page we want to pass this page's ranking to.  
* `image_id`: Refers to the images table ID specifying the page's featured image; this column might not be necessary depending on the template.  
* `status`: Specifies the page publication status: 10 means deleted, 20 means published, 30 means draft, and 40 means scheduled. If the page status is scheduled, the `published_at` column should be filled using the datepicker.  
* `published_at`: Can specify the page publication date.

# password\_resets

* The password recovery process is such that the user is not logged in and goes to the password recovery page. After clicking the password recovery button, an email containing a token and the password reset page link is sent to the user. The user then sets their new password on this page. Since the user's password is hashed, it cannot be recovered, and password recovery means setting a new password.  
* According to company policies, these requests might need to be deleted every X hours/days. Each request creates a new record, regardless of whether the user has previously sent a request, and a new token is sent. The lifespan of this token for password recovery is limited.  
* `email`: Stores the email address for which the password reset request is sent.  
* `token`: Stores the token to ensure that the user has access to this email and received the token in their email.  
* `created_at`: Stores the password reset request date.

# permissions

* `name`: Specifies the permission name.  
* `group`: Used for grouping, e.g., CRM and admin.

# persons

* Stores the list of accounting persons.  
* `name`: Column  
* `mobile`: Column  
* `address`: Column

# products

Used to store products.

* `heading`: Specifies the product title.  
* `slug`: Specifies the unique URL segment of the product.  
* `price`: Stores the selected product amount to avoid fetching all varieties records and comparing to find the selected price, which reduces system performance.  
* `content`: Stores the product page content, edited using an editor with values and images inserted and edited.  
* `title`: Specifies the product page title.  
* `description`: Specifies the product page description.  
* `no_index`: If true or 1, the corresponding product page should have a noindex meta tag.  
* `canonical`: Used to prevent canonicalization issues.  
* `image_id`: specifying the product's featured image.  
* `attribute_group_id`: Specifies the **primary** attribute group that differentiates the varieties of this product (e.g. "Size"). In the admin panel, the available groups are filtered to those linked to the product's category via `attribute_group_category`. Nullable foreign key to `attribute_groups`; set to null when the group is deleted. Additional variation dimensions (e.g. Color) are stored per variety in the `attribute_variety` pivot.  
* `category_id`: Specifies the category of the product. Each category must specify its subcategories, and these subcategories must specify their subcategories, and so on. Required foreign key to `categories`; deletion is restricted while products reference it.  
* `brand_id`: Specifies the brand of the product. Nullable foreign key to `brands`; set to null when the brand is deleted.  
* `minimum`: Specifies the minimum purchase quantity for a product.  
* `maximum`: Specifies the maximum purchase quantity for a product.  
* `step`: Specifies the increment step for purchasing the product, for example, if the product is sold in multiples of 3, this column should have a value of 3\.  
* `profit_percent`: Specifies the profit percentage of ShopFlow from selling this product by different sellers.  
* `has_stock`: Specifies the stock status (default is 1). If this column is false or 0, the product is displayed as out of stock.  
* `variety_counts`: Specifies how many varieties this product has. The difference from stock is that, for example, if there are 4 varieties with the attribute color (e.g., yellow, blue, silver, green) for this product and the yellow variety has 5 units, the blue variety has 6 units, the silver variety has 10 units, and the green variety has 2 units, the `variety_counts` column will have a value of 4 and stock will have a value of 23\.  
* `weight`: Column  
* `length`: Column  
* `width`: Column  
* `height`: Column  
* `status`: Specifies the product status: 10 means deleted, 20 means published, and 30 means draft.  
* `seen`: Stores the product view count.




# product\_images

* For storing product gallery images.  
* The `product_id` column stores the product ID.  
* The `path` column stores the relative path of the images.

# provinces

* For storing provinces.  
* The `name` column specifies the name of the province.

# receipts

* This table is for storing payment receipts.  
* The `user_id` column stores the user ID.  
* The `card_id` column stores the card ID.  
* The `amount` column stores the amount of the receipt.  
* The `order_id` column stores the order ID (nullable).  
* The `tracking_code` column stores the tracking code (nullable).  
* The `paid_at` column stores the payment date (nullable).  
* The `destination_name` column stores the name of the account receiver.  
* The `destination_bank` column stores the name of the bank.  
* The `end_of_card_number` column stores the last four digits of the card number (nullable).  
* The `image_id` column stores the image ID of the receipt (nullable). If the image is not uploaded, the columns `tracking_code`, `paid_at`, and `end_of_card_number`, and `is_paya` must exist in the database.  
* The `description` column stores the description.  
* The `is_paya` column indicates whether this transfer is a Paya transfer (nullable).  
* The `type` column stores the type of receipt: 1-Receipt, 2-Prepayment, 3-Shipping Request (default 1).

# reviews

* Contains user reviews for each product.
* `heading`: The review title written by the user (e.g. "Great product!").
* `content`: The full review text.
* `user_id`: The user who submitted the review. Nullable; set to null if the user is deleted.
* `product_id`: The product being reviewed. Cascade-deletes the review when the product is deleted.
* `variety_id`: The specific variety (e.g. size/color) the user purchased. Nullable; set to null if the variety is deleted.
* `parent_id`: Used to reply to another review (self-referential FK). Set to null when the parent is deleted.
* `status`: Moderation state — `PENDING` (default, hidden from storefront), `APPROVED` (visible), `REJECTED`, `DELETED`.
* Note: no `seller_id` — ShopFlow is single-vendor, there are no sellers.

# roles

* The `name` column specifies the role name.  
* The `group` column specifies the role category.

# role\_permissions

* The `role_id` column refers to the user role record.  
* The `permission_id` column refers to the permission record.

# settings

* Contains various site settings.  
* The `key` column specifies the English key for various settings.  
* The `label` column specifies the Persian label for various settings.  
* The `content` column stores the value for various settings.  
* The `autoload` column specifies whether the setting should be loaded globally. If a setting does not need to be global, this column should be false, and it should be cached.

# shipping\_cities

* For storing shipping methods for each city.  
* The `province_id` column refers to the province where this method is active (nullable).  
* The `city_id` column refers to the city where this method is active (nullable). One of the columns `province_id` or `city_id` must be specified.  
* The `shipping_method_id` column refers to the `id` column in the `shipping_methods` table and specifies the related shipping method.  
* The `pay_on_delivery` column is a boolean indicating if payment is due upon delivery, meaning no online payment is needed.  
* The `amount` column specifies the shipping cost. For postpaid, this column is null. For free shipping, this column is zero.  
* The `sending_days` column specifies the shipping days limitation, which can be a single day, even/odd days, or any pattern specified later in related constants.  
* The `disable_from` and `disable_to` columns specify the period when shipping is not available.  
* The `delay` column specifies the delivery delay in days.  
* The `description` column includes details such as the shipping time, for example, "up to 72 hours" or "from 18:00 to 22:00 on the current day."  
* The `status` column is a boolean indicating if this method is active or inactive.

# shipping\_lines

Represents a shipping carrier or company (e.g. Post Office, Express Courier, Same-day Delivery). Acts as the top level of the shipping hierarchy: `shipping_lines → shipping_methods → shipping_cities`.

* `name`: The carrier name shown to admins (e.g. "Post", "Express Courier").
* `cost`: Base cost for this carrier. Can be overridden per city in `shipping_cities`.

# shipping\_methods

A specific service tier offered by a shipping carrier. References `shipping_lines` and is further scoped per city via `shipping_cities`.

* `shipping_line_id`: The carrier this method belongs to (FK to `shipping_lines`). Added during ShopFlow implementation — not in the original doc.
* `name`: The service name, e.g. "Standard Post", "Overnight Express".
* `type`: Service type, e.g. Express, Special, Economy.
* `min_count`: Minimum purchase quantity required to use this method.
* `min_amount`: Minimum purchase amount required to use this method.
* `for`: Audience — `CUSTOMER=10` (default), `PARTNER=20`, `EMPLOYEE=30`. Implemented as `ShippingMethodForEnum`.
* `disable_from` / `disable_to`: Period during which this method is unavailable.
* `status`: Active or inactive.

# sliders

* For creating various sliders.  
* `name`: Human-readable label for the slider, e.g. "Home Page Main Slider." Not shown on the frontend.  
* `position`: The key used by the frontend to fetch this slider, e.g. "home-main". Must be unique per placement.  
* `status`: Publication status — 10 for deleted, 20 for published (default), 30 for draft.  
* Deleting a slider cascades to its slides (and their images).

# slides

* Contains the slides for each slider.  
* The `slider_id` column specifies which slider each slide belongs to. Foreign key; cascades on slider delete.  
* The `heading` column specifies the title or alt text (for images) of the slide. Nullable.  
* The `label` column can be used for a secondary text or alt text for the image if each slide has a title. Nullable.  
* The `url` column specifies the link that opens when clicking on the slider slides. Nullable.  
* The `order` column specifies the slide execution order (default 0). Lower values appear first.  
* The image is stored via the polymorphic `images` table (`imageable_type = Slide`, `imageable_id = slide.id`) using a `MorphOne` relationship — no `image_id` column on slides. Deleting a slide cascades to its image.

# sources

* For storing accounting sources, such as sales, documents, and sales returns.  
* The `name` column stores the source name.  
* The `type` column stores the type (account, bank, person).  
* The `nature` column specifies the nature, storing debits and credits: 1 for debit, 2 for credit, and 3 for both debit/credit.  
* The `coding` column stores the coding.  
* The `parent_coding` column stores the parent coding.

# sub\_orders

* Stores order information for each seller.  
* The `order_id` column.  
* The `factor_user_id` column stores the invoicing user.  
* The `gather_id` column stores the gatherer.  
* The `checker_id` column stores the checker.  
* The `courier_id` column stores the courier (currently for hardware only).  
* The `invoice_number` column stores the invoice number.  
* The `invoiced_at` column stores the invoice date.  
* The `sent_at` column stores the sent time (currently for hardware only).  
* The `received_at` column stores the received date.  
* The `gathered_at` column stores the gathering date.  
* The `checked_at` column stores the checking date.  
* The `pack_count` column stores the package count.  
* The `item_count` column stores the total item count.  
* The `printed_at` column stores the print date.  
* The `total` column stores the total amount.  
* The `is_subset` column is a boolean indicating whether it belongs to the shop flow.  
* The `seller_id` column stores the seller ID.

# sub\_order\_logs

* The `sub_order_id` column refers to the `sub_orders` table.  
* The `user_id` column specifies which user the log belongs to.  
* The `seller_id` column specifies which seller the log belongs to.  
* The `status` column stores the log status.  
* The `started_at` column stores the start date.  
* The `ended_at` column stores the end date.

# tickets

* Stores system tickets exchanged between users, managers, sellers, and administrators.  
* Each ticket belongs to a specific department, specified by the `department_id` column.  
* The `user_id` column specifies which user the ticket belongs to.  
* The `seller_id` column specifies which seller the ticket belongs to.  
* The `heading` column specifies the ticket title.  
* The `priority` column specifies the ticket priority: low, medium, high, and urgent. The priority is relative to other tickets of the same user, not all users. Value 10 means low priority, 20 means medium, 30 means high, and 40 means urgent.  
* The `status` column specifies the ticket status: 10 means closed, 20 means open, 30 means replied by managers, 40 means replied by users, and 50 means follow-up required.  
  * Ticket lifecycle: When a ticket is opened, it is in the open status. When a manager responds, the status changes to 50, and when a user responds, it changes to 30\. When more time is needed, the manager changes the status to 50, and when the issue is resolved, the status changes to 10\.  
* The `flagged_id` column stores the ID of the manager to whom the ticket is flagged for follow-up.  
* The `seen_at` column stores the time the ticket was seen by the other party, for example, the time a ticket created by

# ticket\_attachments

Contains the attachments for each message. Attachments can be multimedia, like images, PDFs, etc.

* `message_id`: Specifies which ticket response this attachment belongs to.  
* `client_name`: The name given to the file during upload, displayed in the ticket view.  
* `name`: Specifies the new name of the file.  
* `mime`: Specifies the MIME type of the file.  
* `path`: Specifies the file path, which can also be renamed to `path`.

# ticket\_messages

Contains messages related to the ticket.

* `ticket_id`: Indicates which ticket this response belongs to. All responses are ordered chronologically (direct or reverse).  
* `user_id`: Indicates which user sent this message.  
* `seller_id`: Indicates which seller sent this message. A message can belong to either a seller or a user at the same time.  
* `content`: Stores the text of the message.  
* `star`: Indicates the rating of this answer by the user, a number between 1 and 5 representing the number of stars.  
* `seen_at`: Indicates when the message was seen by the other party (admin for user messages and user for admin messages).  
* `is_admin`: Indicates if the user who sent this message is an admin.

# ticket\_permissions

Contains permissions related to the ticket.

* `user_id`: Stores the ID of the user to whom access is to be granted.  
* `allowed_user_id`: Stores the target user's ID.

# users

For storing users and admins.

* `first_name`: Stores the user's first name.  
* `last_name`: Stores the user's last name.  
* `email`: Used to store the user's email address.  
* `email_verified_at`: Stores the email verification time of the user. If a user lacks an email or hasn't verified their email or changes their email without verifying the new one, this column changes to null.  
* `mobile`: Used to store the user's mobile number.  
* `mobile_verified_at`: Stores the time the user's mobile number was verified. If the user changes their mobile number, this column remains null until re-verified.  
* `national_id`: Used to store the user's national ID. This column cannot be changed in the future by the user because a user cannot request a change of national ID from the Civil Registration Office. The national ID must be verified for correct pattern.  
* `password`: Used to store the user's hashed password.  
* `login_token`: Used for admin login.  
* `login_token_expire_time`: Stores the expiration date of the token stored in `login_token`.

# user\_configs

This table is used to store user-related settings.

* `user_id`:  
* `key`: Used to store the settings identifier.  
* `label`: The Persian title of various settings.  
* `content`: Used to store the settings value.  
* `autoload`: If any settings are not needed globally, this column should be false, and this column should be cached.

# user\_roles

For storing each user's role.

* `user_id`: Indicates which user this role belongs to.  
* `seller_id`: Indicates which seller this role belongs to.  
* `role_id`: Indicates which user role this belongs to.

# user\_sources

For user access to accounts.

* `user_id`:  
* `source_coding`:  
* `type`: For access type (1 create, 2 read).

# user\_statuses

To restrict the user to a status.

* `class`: The status class.  
* `property`: The property name.  
* `user_id`:  
* `key`: The key for the state that access is allowed.

# user\_permissions

For storing user permissions and removing role-based permissions.

* `user_id`: Indicates which user this permission belongs to.  
* `seller_id`: Indicates which seller this permission belongs to.  
* `permission_id`: Indicates which permission this belongs to.  
* `active`: If 1, this permission is added to the user; if 0, this permission was given by a user role and is now removed (user roles do not appear here and are in the `role_permission` table).

# varieties

Contains product variations entered by the seller on the site. This table creates a record depending on the selected variation and price.

* `product_id`: Indicates which product this variety belongs to. Foreign key to `products`; cascades on product delete.
* `attribute_id`: Links to the `attributes` table - the specific attribute that defines this variety (e.g. "Red", "XL"). Nullable FK; set to null when the attribute is deleted. When set, `attribute_value` and `color` are auto-populated from the linked attribute via model saving event.
* `attribute_value`: Display label for the variation, auto-populated from `attribute.value` when `attribute_id` is set. Can also be set manually when no attribute is linked.
* `color`: Hex or name color for the variation, auto-populated from `attribute.color` when `attribute_id` is set.
* `price`  
* `sale_price`   
* `inventory`: Number of ShopFlow inventory, the default value is 0\.  
* `has_stock`: Indicates the stock status (default is 1/true). If this column is false or 0, the variety is displayed as out of stock.  
* `status`: Publication status of the variety.

# attribute\_variety (variety\_attribute pivot)

Stores additional attribute associations for each variety, enabling multi-dimensional varieties (e.g. Size as primary + Color as secondary).

* Table name is `attribute_variety` (Laravel alphabetical convention).
* `variety_id`: FK to `varieties`; cascades on variety delete.
* `attribute_id`: FK to `attributes`; cascades on attribute delete.
* The pair `(variety_id, attribute_id)` is unique. Carries `created_at` / `updated_at`.
* The primary attribute is still on `varieties.attribute_id` (drives `attribute_value` and `color` auto-population). This pivot holds **additional** attributes from other groups (e.g. when the product's `attribute_group_id` is "Size", color attributes are attached here).
* **Frontend query pattern:** to find a specific Size+Color combination, join `varieties` with `attribute_variety` filtering on both `varieties.attribute_id` (size) and `attribute_variety.attribute_id` (color). Cache all varieties with their pivot attributes under `varieties.product.{product_id}` and resolve combinations in memory to avoid N+1.

# variety\_serials

This table is used to store product serials.

* `variety_id`:  
* `serials`: Stores the product serial.  
* `created_at`:

# variety\_details

This table is used to store product order reports in different time periods.

* `variety_id`:  
* `order_1`: Stores the number of sales in the past 7 days (temporary period).  
* `order_2`: Stores the number of sales in the past 30 days (temporary period).  
* `order_3`: Stores the number of sales in the past 90 days (temporary period).  
* `checked_at`: Stores the report update time.

# warehouses

For storing warehouses. Warehouses can determine the location of each product, and each variety has a `warehouse_id` column.

* `name`: Specifies the warehouse name (the name can be written in any form according to ShopFlow policy, for example, Valiasr Electronics Warehouse or Raad, etc.).  
* `location`: Stores the warehouse address.  
* `phone`: Stores the warehouse phone number.  
* `latitude` and `longitude`: Store the warehouse's geographic coordinates.

# wishlists

Stores the products saved to user wishlists.

* `user_id`: The user who saved the product. Cascade-deletes the entry when the user is deleted.
* `product_id`: The saved product. Cascade-deletes the entry when the product is deleted.
* Unique constraint on `(user_id, product_id)` — a user can only save each product once.
* Admin resource is read-only (list + delete); entries are created by users on the storefront.

# working\_hours

This table is used to store the monthly working hours of ShopFlow employees.

* `user_id`: Indicates which user this record belongs to.  
* `entered_at`: The user's entry date and time.  
* `exited_at`: The user's exit date and time.

# organizational\_requests

This table is used to store organizational requests.

* `full_name`:  
* `phone`:  
* `email`:  
* `company_name`:  
* `description`:  
* `attachment`: Stores the file.  
* `status`: Stores the statuses, default is 10\.  
* `created_at`:  
* `updated_at`:

# partner\_requests

This table is used to store partnership requests.

* `user_id`: Stores the user ID.  
* `guild_id`: Stores the guild ID.  
* `image_id`: Stores the image ID.  
* `status`: Stores the request status.  
* `created_at`:  
* `updated_at`:

# contact\_us

This table is used to store "Contact Us" information.

* `subject`: Stores the subject.  
* `full_name`:  
* `phone`:  
* `description`:  
* `status`: Stores the statuses, default is 10\.  
* `created_at`:  
* `updated_at`:

# tags

This table is for storing tags.

* `slug`: Stores the tag's slug.  
* `name`: Stores the tag's name.  
* `category_id`: Stores the category ID.  
* `attribute_id`: Stores the attribute ID.  
* `content`: Stores the content related to the tag.  
* `type`: Specifies the type of the question, for example, for users or sellers.  
* `created_at`: Stores the creation date.

# points

This table is for storing points.

* `user_id`: Stores the user ID.  
* `point`: Stores the point amount, which can also be negative (signed).  
* `action`: Stores the action.  
* `product_id`: Stores the product ID (nullable).  
* `review_id`: Stores the comment ID (nullable).  
* `order_id`: Stores the order ID (nullable).  
* `brand_id`: Stores the brand ID (nullable).  
* `category_id`: Stores the category ID (nullable).  
* `description`: Stores the description (nullable).  
* `created_at`: Stores the creation date.

# redirects

* `source`: Stores the source path.  
* `target`: Stores the target path.  
* `is_external`: Indicates whether the target is internal or external.  
* `is_exact`: Indicates whether the source path is exact or relative.  
* `is_active`: Indicates whether the redirect is active or inactive.

# newsletters

* `user_id`: Stores the user ID.  
* `mobile`: Stores the mobile number.

# saved\_cards

* `card_number`: Stores the card number.  
* `account_number`: Stores the account number.  
* `iban_number`: Stores the IBAN number.  
* `bank_name`: Stores the bank name.  
* `account_owner`: Stores the account owner's name.  
* `description`: Stores the description.

# notifications

This table is for storing notifications.

* `user_id`: Stores the user ID of the recipient.  
* `message`: Stores the notification message.  
* `seen_at`: Stores the time when the notification was seen.

# order\_notes

This table is for storing order notes.

* `user_id`: Stores the user ID of the person who created the note.  
* `order_id`: Stores the order ID.  
* `content`: Stores the note content.

# emalls\_products

This table is for storing eMalls products.

* `title`: Stores the product title.  
* `first_price`: Stores the initial price.  
* `techno_price`: Stores the ShopFlow price.  
* `hamkar_price`: Stores the partner price.  
* `techno_url`: Stores the product URL on the ShopFlow site.  
* `emalls_url`: Stores the product URL on the eMalls site.  
* `category`: Stores the category.  
* `brand`: Stores the brand.  
* `min_profit_type`: Stores the minimum profit type (percentage/fixed).  
* `min_profit_value`: Stores the minimum profit value.  
* `max_profit_type`: Stores the maximum profit type.  
* `max_profit_value`: Stores the maximum profit value.  
* `margin`: Stores the margin.  
* `check_period`: Stores the product check period (e.g., every 5 minutes).  
* `checked_at`: Stores the time when the product was checked.  
* `next_check_time`: Stores the next product check time.

# short\_urls

This table is for storing short links.

* `id`: Stores the ID.  
* `source`: Stores the source URL.  
* `destination`: Stores the destination URL.  
* `created_at`: Stores the creation date.  
* `updated_at`: Stores the update date.

# bank\_sms

This table is for storing bank SMS messages.

* `id`: Stores the ID.  
* `name`: Stores the SMS title.  
* `body`: Stores the SMS content.  
* `client`: Stores the device name.  
* `received_at`: Stores the SMS receipt time.  
* `created_at`: Stores the creation date.

# system\_notifications

This table is for storing system notifications.

* `id`: Stores the ID.  
* `section`: Stores the notification section.  
* `user_id`: Stores the user ID who created the notification.  
* `title`: Stores the notification title.  
* `description`: Stores the notification description.  
* `seen_by`: Stores the ID of the person who saw the notification.  
* `seen_at`: Stores the time when the notification was seen.  
* `created_at`: Stores the creation date.  
* `updated_at`: Stores the update date.

# user\_category\_percent

This table is for storing user price increase percentages for each category.

* `id`: Stores the ID.  
* `category_id`: Stores the category ID.  
* `percent`: Stores the price increase percentage.  
* `created_at`: Stores the creation date.  
* `updated_at`: Stores the update date.

# user\_price\_conditions

This table is for excluding sellers or categories from user price updates based on defined percentages.

* `id`: Stores the ID.  
* `category_id`: Stores the category ID.  
* `seller_id`: Stores the seller ID.  
* `created_at`: Stores the creation date.  
* `updated_at`: Stores the update date.

---

# Tables needed in the future:

* Customer Club  
* CRM  
* Check Receipt Section  
* DigiKala Orders Section  
* Warranty Repair Cost Change Section  
* Tables for storing check and warranty images

---

# For review:

The proposed `transaction_details` table, which I will review later, includes columns for debtor, creditor, balance, `source_id`, `user_id`, and `type` (which can be user, seller, or source) and `transaction_id`.

The proposed `wallets` table includes columns for `user_id`, amount, debtor, and creditor. However, currently, the balance is in the `users` table.

Records: payment, order, credit, admin\_fixation, future\_balance, affilable, and perhaps scheduled.

The order page includes the following:

* Order Details: order ID, invoice/accounting document number, customer, email, phone, total, order status, description, IP address, user OS, add date, edit date  
* Payment Details: first name, last name, address, city, state, postal code, payment method  
* Products: table including: product, product name, quantity, unit price, total, and at the bottom of the table: subtotal, 9% VAT, fixed tax rate, total  
* History: add date, description, status, notify customer \- along with a button to add customer history  
* Order Status (as select), notify customer (checkbox), description (textarea) and add history button  
* Add RBAC columns

