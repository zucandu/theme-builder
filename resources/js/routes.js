const Storefront = () => import('./components/themes/default/Storefront.vue');
const Index = () => import('./components/themes/default/storefront/Index.vue');
const Login = () => import('./components/themes/default/storefront/Login.vue');
const Register = () => import('./components/themes/default/storefront/Register.vue');
const Logout = () => import('./components/themes/default/storefront/Logout.vue');
const ContactUs = () => import('./components/themes/default/storefront/ContactUs.vue');
const Cart = () => import('./components/themes/default/storefront/Cart.vue');
const Checkout = () => import('./components/themes/default/storefront/Checkout.vue');
const CheckoutSuccess = () => import('./components/themes/default/storefront/CheckoutSuccess.vue');

const TrackOrder = () => import(`./components/themes/default/storefront/TrackOrder.vue`);
const TrackOrderForm = () => import(`./components/themes/default/storefront/TrackOrderForm.vue`);
const TrackOrderDetails = () => import(`./components/themes/default/storefront/TrackOrderDetails.vue`);

const ReturnExchange = () => import(`./components/themes/default/storefront/ReturnExchange.vue`);
const ReturnExchangeForm = () => import(`./components/themes/default/storefront/ReturnExchangeForm.vue`);
const ReturnExchangeProcess = () => import(`./components/themes/default/storefront/ReturnExchangeProcess.vue`);

const PasswordForgotten = () => import('./components/themes/default/storefront/PasswordForgotten.vue');
const PasswordReset = () => import('./components/themes/default/storefront/PasswordReset.vue');
const Invoice = () => import('./components/themes/default/storefront/Invoice.vue');
const Unsubscribe = () => import('./components/themes/default/storefront/Unsubscribe.vue');
const PageNotFound = () => import('./components/themes/default/storefront/PageNotFound.vue');

const Account = () => import('./components/themes/default/storefront/Account.vue');
const AccountProfile = () => import(`./components/themes/default/storefront/AccountProfile.vue`);
const AccountPassword = () => import(`./components/themes/default/storefront/AccountPassword.vue`);
const AccountAddressBook = () => import(`./components/themes/default/storefront/AccountAddressBook.vue`);
const AccountAddressBookNew = () => import(`./components/themes/default/storefront/AccountAddressBookNew.vue`);
const AccountAddressBookEdit = () => import(`./components/themes/default/storefront/AccountAddressBookEdit.vue`);
const AccountOrderList = () => import(`./components/themes/default/storefront/AccountOrderList.vue`);
const AccountOrderDetails = () => import(`./components/themes/default/storefront/AccountOrderDetails.vue`);

const Category = () => import('./components/themes/default/storefront/Category.vue');
const Manufacturer = () => import('./components/themes/default/storefront/Manufacturer.vue');
const SearchResult = () => import('./components/themes/default/storefront/SearchResult.vue');
const Product = () => import('./components/themes/default/storefront/Product.vue');
const ProductReviewWrite = () => import('./components/themes/default/storefront/ProductReviewWrite.vue');

const ArticleDetails = () => import('./components/themes/default/storefront/ArticleDetails.vue');

const Blog = () => import('./components/themes/default/storefront/Blog.vue');
const BlogListing = () => import(`./components/themes/default/storefront/BlogListing.vue`);
const BlogSearch = () => import(`./components/themes/default/storefront/BlogSearch.vue`);
const BlogCategory = () => import(`./components/themes/default/storefront/BlogCategory.vue`);
const BlogAuthor = () => import(`./components/themes/default/storefront/BlogAuthor.vue`);

const PaymentRequest = () => import('./components/themes/default/storefront/PaymentRequest.vue');


// Check if selectedLanguage is in localStorage, else fall back to default_language
const selectedLang = localStorage.getItem('selectedLanguage') || zucConfig.default_language;

// Determine the language part of the route
const lang = selectedLang === zucConfig.default_language ? '' : selectedLang;

const storefrontChildRoutes = [
    {
        path: ``,
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
        path: 'account',
        name: 'account',
        redirect: { name: 'account_order_list' },
        component: Account,
        children: [
            {
                path: 'profile',
                name: 'account_profile',
                component: AccountProfile
            },
            {
                path: 'order/list',
                name: 'account_order_list',
                component: AccountOrderList
            },
            {
                path: 'order/:ref',
                name: 'account_order_details',
                component: AccountOrderDetails
            },
            {
                path: 'password',
                name: 'account_password',
                component: AccountPassword
            },
            {
                path: 'address-book',
                name: 'account_address_book',
                component: AccountAddressBook
            },
            {
                path: 'address-book/new',
                name: 'account_address_book_new',
                component: AccountAddressBookNew
            },
            {
                path: 'address-book/:id',
                name: 'account_address_book_edit',
                component: AccountAddressBookEdit
            },
        ]
    },
    {
        path: 'category/:slug',
        name: 'category',
        component: Category
    },
    {
        path: 'manufacturer/:slug',
        name: 'manufacturer',
        component: Manufacturer
    },
    {
        path: 'search-result',
        name: 'search',
        component: SearchResult
    },
    {
        path: 'product/:slug',
        name: 'product',
        component: Product
    },
    {
        path: 'product-review-write/:slug',
        name: 'product_review_write',
        component: ProductReviewWrite
    },
    {
        path: 'logout',
        name: 'logout',
        component: Logout
    },
    {
        path: 'cart',
        name: 'cart',
        component: Cart
    },
    {
        path: 'checkout',
        name: 'checkout',
        component: Checkout
    },
    {
        path: 'checkout-success/:ref',
        name: 'checkout_success',
        component: CheckoutSuccess
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
        path: 'article/:slug',
        name: 'article_details',
        component: ArticleDetails
    },
    {
        path: 'blog',
        name: 'blog',
        component: Blog,
        redirect: { name: 'blog_listing' },
        children: [
            {
                path: 'listing',
                name: 'blog_listing',
                component: BlogListing
            },
            {
                path: 'search',
                name: 'blog_search',
                component: BlogSearch
            },
            {
                path: 'category/:slug',
                name: 'blog_category',
                component: BlogCategory
            },
            {
                path: 'author/:slug',
                name: 'blog_author',
                component: BlogAuthor
            }
        ]
    },
    {
        path: 'forgot-password',
        name: 'forgot_password',
        component: PasswordForgotten
    },
    {
        path: 'reset-password/:token',
        name: 'reset_password',
        component: PasswordReset
    },
    {
        path: 'invoice/:ref',
        name: 'invoice',
        component: Invoice
    },
    {
        path: 'pay/:token',
        name: 'pay',
        component: PaymentRequest
    },
    {
        path: 'unsubscribe',
        name: 'unsubscribe',
        component: Unsubscribe
    }, {
        path: 'page-not-found',
        name: 'page_not_found',
        component: PageNotFound
    },
    {
        path: ':pathMatch(.*)*',
        redirect: '/page-not-found'
    },
]

export const routes = [
    {
        path: `/${lang}`,
        component: Storefront,
        children: storefrontChildRoutes
    }
]