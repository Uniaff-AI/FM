// routesIndex.js

const routesIndex = [
    {
        name: 'DynamicPage',
        path: '/page/:slug',
        component: () => import(/* webpackChunkName: "chunks/dynamic-page" */ '../views/Frontpage/DynamicPage'),
        meta: {
            requiresAuth: false,
        },
    },
    {
        name: 'ContactUs',
        path: '/contact-us',
        component: () => import(/* webpackChunkName: "chunks/contact-us" */ '../views/Frontpage/ContactUs'),
        meta: {
            requiresAuth: false,
        },
    },
    {
        name: 'Demo',
        path: '/demo',
        component: () => import(/* webpackChunkName: "chunks/demo" */ '../views/Demo'),
        meta: {
            requiresAuth: false,
        },
    },
]

export default routesIndex
