import { createRouter, createWebHistory, type RouteRecordRaw } from "vue-router";
import Errorpagesinfo from "../shared/layouts/errorpagesinfo.vue";
import Maindashboard from "../shared/layouts/maindashboard.vue";
import Authlayout from "../shared/layouts/authlayout.vue";


const routes: RouteRecordRaw[] = [

  {
    path: '/',
    component: Authlayout,
    children: [
      {
        path: '',
        name: 'Login',
        component: () => import("../components/auth/login.vue")
      }
    ]
  },

  {
    path: '/',
    component: Maindashboard,
    children:
      [
        {
          path: `dashboards`,
          name: 'Dashboards',
          children: [
            {
              path: 'sales',
              name: "Sales",
              component: () => import("../components/dashboards/sales.vue"),
            },
          ],
        },
      ],
  },


 

  // earrorpage
  {
    path: `/pages/error`,
    component: Errorpagesinfo,
    children: [
      {
        path: "404-error",
        component: () => import("../components/pages/error/404-error.vue"),
      },
    ]
  }
];


const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
});

export default router;

