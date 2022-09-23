const Storefront = () => import(`./components/themes/Storefront`)
const Index = () => import(`./components/themes/storefront/Index`)
const Login = () => import(`./components/themes/storefront/Login`);
const Register = () => import(`./components/themes/storefront/Register`);
const Logout = () => import(`./components/themes/storefront/Logout`);
const ContactUs = () => import(`./components/themes/storefront/ContactUs`);
const TrackOrder = () => import(`./components/themes/storefront/TrackOrder`);
const TrackOrderForm = () => import(`./components/themes/storefront/TrackOrderForm`);
const TrackOrderDetails = () => import(`./components/themes/storefront/TrackOrderDetails`);
const PasswordForgotten = () => import(`./components/themes/storefront/PasswordForgotten`);
const PasswordReset = () => import(`./components/themes/storefront/PasswordReset`);
const StorefrontInvoice = () => import(`./components/themes/storefront/Invoice`);
const ReturnExchange = () => import(`./components/themes/storefront/ReturnExchange`);
const ReturnExchangeForm = () => import(`./components/themes/storefront/ReturnExchangeForm`);
const ReturnExchangeProcess = () => import(`./components/themes/storefront/ReturnExchangeProcess`);

const Account = () => import(`./components/themes/storefront/Account`);
const AccountProfile = () => import(`./components/themes/storefront/AccountProfile`);
const AccountOrders = () => import(`./components/themes/storefront/AccountOrders`);
const AccountPassword = () => import(`./components/themes/storefront/AccountPassword`);
const AccountAddressBook = () => import(`./components/themes/storefront/AccountAddressBook`);
const AccountAddressBookDetails = () => import(`./components/themes/storefront/AccountAddressBookDetails`);
const AccountAddressBookAddNew = () => import(`./components/themes/storefront/AccountAddressBookAddNew`);
const AccountAddressBookEdit = () => import(`./components/themes/storefront/AccountAddressBookEdit`);
const AccountOrderList = () => import(`./components/themes/storefront/AccountOrderList`);
const AccountOrderDetails = () => import(`./components/themes/storefront/AccountOrderDetails`);

const StorefrontCategory = () => import(`./components/themes/storefront/Category`);
const StorefrontManufacturer = () => import(`./components/themes/storefront/Manufacturer`);
const StorefrontProduct = () => import(`./components/themes/storefront/Product`);
const StorefrontProductReviewWrite = () => import(`./components/themes/storefront/ProductReviewWrite`);
const StorefrontCart = () => import(`./components/themes/storefront/Cart`);
const StorefrontSearchResult = () => import(`./components/themes/storefront/SearchResult`);
const PageNotFound = () => import(`./components/themes/storefront/PageNotFound`);
const StorefrontCheckout = () => import(`./components/themes/storefront/Checkout`);
const StorefrontCheckoutSuccess = () => import(`./components/themes/storefront/CheckoutSuccess`);

const BlogPost = () => import(`./components/themes/storefront/Blog`);
const BlogPostListing = () => import(`./components/themes/storefront/BlogPostListing`);
const BlogPostCategoryListing = () => import(`./components/themes/storefront/BlogPostCategoryListing`);
const BlogSearch = () => import(`./components/themes/storefront/BlogSearch`);
const BlogPostDetails = () => import(`./components/themes/storefront/BlogPostDetails`);

const storefrontChildRoutes = [
    {
        path: '/',
        name: 'index',
        component: Index,
    },
    {
        path: 'login',
        name: 'login',
        component: Login
    },
    {
        path: 'register',
        name: 'register',
        component: Register
    },
    {
        path: 'forgot-password',
        name: 'password_forgotten',
        component: PasswordForgotten
    },
    {
        path: 'reset-password/:token',
        name: 'reset_password',
        component: PasswordReset
    },
    {
        path: 'account',
        name: 'account',
        redirect: '/account/orders',
        component: Account,
        children: [
            {
                path: 'profile',
                name: 'account_profile',
                component: AccountProfile
            },
            {
                path: 'orders',
                name: 'account_orders',
                redirect: '/account/orders/list',
                component: AccountOrders,
                children: [
                    {
                        path: 'list',
                        name: 'account_order_list',
                        component: AccountOrderList
                    },
                    {
                        path: 'details/:ref',
                        name: 'account_order_details',
                        component: AccountOrderDetails
                    },
                ]
            },
            {
                path: 'password',
                name: 'account_password',
                component: AccountPassword
            },
            {
                path: 'address-book',
                name: 'account_address_book',
                redirect: '/account/address-book/details',
                component: AccountAddressBook,
                children: [
                    {
                        path: 'details',
                        name: 'account_address_book_details',
                        component: AccountAddressBookDetails
                    },
                    {
                        path: 'add-new-address',
                        name: 'account_address_book_add_new',
                        component: AccountAddressBookAddNew
                    },
                    {
                        path: 'edit/:id',
                        name: 'account_address_book_edit',
                        component: AccountAddressBookEdit
                    }
                ]
            },
        ]
    },
    {
        path: 'invoice/:ref',
        name: 'invoice',
        component: StorefrontInvoice
    },
    {
        path: 'category/:slug',
        name: 'category',
        component: StorefrontCategory
    },
    {
        path: 'manufacturer/:slug',
        name: 'manufacturer',
        component: StorefrontManufacturer
    },
    {
        path: ':productslug',
        name: 'product',
        component: StorefrontProduct
    },
    {
        path: 'product-review-write/:productslug',
        name: 'product_review_write',
        component: StorefrontProductReviewWrite
    },
    {
        path: 'search-result/:keyword',
        name: 'search_result',
        component: StorefrontSearchResult
    },
    {
        path: 'page-not-found',
        name: 'page_not_found',
        component: PageNotFound
    },
    {
        path: 'cart',
        name: 'cart',
        component: StorefrontCart
    },
    {
        path: 'checkout',
        name: 'checkout',
        component: StorefrontCheckout
    },
    {
        path: 'checkout-success/:ref',
        name: 'checkout_success',
        component: StorefrontCheckoutSuccess
    },
    {
        path: 'logout',
        name: 'logout',
        component: Logout
    },
    {
        path: 'contact-us',
        name: 'contact_us',
        component: ContactUs
    },
    {
        path: 'track-order',
        name: 'track_order',
        redirect: '/track-order/form',
        component: TrackOrder,
        children: [
            {
                path: 'form',
                name: 'track_order_form',
                component: TrackOrderForm
            },
            {
                path: ':ref',
                name: 'track_order_details',
                component: TrackOrderDetails
            }
        ]
    },
    {
        path: 'return-exchange',
        name: 'return_exchange',
        redirect: '/return-exchange/form',
        component: ReturnExchange,
        children: [
            {
                path: 'form',
                name: 'return_exchange_form',
                component: ReturnExchangeForm
            },
            {
                path: ':ref',
                name: 'return_exchange_process',
                component: ReturnExchangeProcess
            }
        ]
    },
    {
        path: 'blog',
        name: 'blog',
        redirect: '/blog/posts',
        component: BlogPost,
        children: [
            {
                path: 'posts',
                name: 'blog_posts',
                component: BlogPostListing
            },
            {
                path: 'category/:slug',
                name: 'blog_post_category',
                component: BlogPostCategoryListing
            },
            {
                path: 'search',
                name: 'blog_search',
                component: BlogSearch
            }
        ]
    },
    {
        path: 'article/:slug',
        name: 'blog_post_details',
        component: BlogPostDetails
    },
    {
        path: ':pathMatch(.*)*', 
        redirect: '/page-not-found'
    },
]

export const routes = [
    {
        path: '/',
        component: Storefront,
        children: storefrontChildRoutes
    },
]