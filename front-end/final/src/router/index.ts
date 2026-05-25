import {
  createRouter,
  createWebHistory,
  type RouteRecordRaw,
} from "vue-router";
import Errorpagesinfo from "../shared/layouts/errorpagesinfo.vue";
import Landingpage from "../shared/layouts/landingpage.vue";
import Maindashboard from "../shared/layouts/maindashboard.vue";
import Authlayout from "../shared/layouts/authlayout.vue";

const routes: RouteRecordRaw[] = [
  {
    path: "/",
    component: Authlayout,
    children: [
      {
        path: "",
        name: "Login",
        component: () => import("../view/auth/index.vue"),
      },
      {
        path: "logout",
        name: "Logout",
        beforeEnter: async (_to, _from, next) => {
          const { useAuthStore } = await import("../stores/auth");
          const authStore = useAuthStore();
          await authStore.logoutUser();
          next("/");
        },
        component: () => import("../view/auth/logout.vue"),
      },
    ],
  },

  {
    path: "/",
    component: Maindashboard,
    children: [
      // {
      //   path: '',
      //   redirect: '/dashboards/sales',
      // },
      //Dashboard
      {
        path: `dashboards`,
        name: "Dashboards",
        children: [
          {
            path: "sales",
            name: "Sales",
            component: () => import("../components/dashboards/sales.vue"),
          },
          {
            path: "analytics",
            name: "Analytics",
            component: () => import("../components/dashboards/analytics.vue"),
          },
          {
            path: "ecommerce",
            name: "Ecommerce",
            children: [
              {
                path: "ecommerce-dashboard",
                name: "Ecommerce Dashboard",
                component: () =>
                  import("../components/dashboards/ecommerce/dashboard.vue"),
              },
              {
                path: "products",
                name: "Products",
                component: () =>
                  import("../components/dashboards/ecommerce/products.vue"),
              },
              {
                path: "product-details",
                name: "Product Details",
                component: () =>
                  import(
                    "../components/dashboards/ecommerce/product-details.vue"
                  ),
              },
              {
                path: "cart",
                name: "Cart",
                component: () =>
                  import("../components/dashboards/ecommerce/cart.vue"),
              },
              {
                path: "checkout",
                name: "Checkout",
                component: () =>
                  import("../components/dashboards/ecommerce/checkout.vue"),
              },
              {
                path: "customers",
                name: "Customers",
                component: () =>
                  import("../components/dashboards/ecommerce/customers.vue"),
              },
              {
                path: "orders",
                name: "Orders",
                component: () =>
                  import("../components/dashboards/ecommerce/orders.vue"),
              },
              {
                path: "order-details",
                name: "Order Details",
                component: () =>
                  import(
                    "../components/dashboards/ecommerce/order-details.vue"
                  ),
              },
              {
                path: "add-product",
                name: "Add Product",
                component: () =>
                  import("../components/dashboards/ecommerce/add-product.vue"),
              },
            ],
          },
          {
            path: "crypto",
            name: "Crypto",
            children: [
              {
                path: "crypto-dashboard",
                name: "Crypto Dashboard",
                component: () =>
                  import("../components/dashboards/crypto/dashboard.vue"),
              },
              {
                path: "transactions",
                name: "Transactions",
                component: () =>
                  import("../components/dashboards/crypto/transactions.vue"),
              },
              {
                path: "currency-exchange",
                name: "Currency Exchange",
                component: () =>
                  import(
                    "../components/dashboards/crypto/currency-exchange.vue"
                  ),
              },
              {
                path: "buy-sell",
                name: "Buy $ Sell",
                component: () =>
                  import("../components/dashboards/crypto/buy-sell.vue"),
              },
              {
                path: "market-cap",
                name: "Marketcap",
                component: () =>
                  import("../components/dashboards/crypto/market-cap.vue"),
              },
              {
                path: "wallet",
                name: "Wallet",
                component: () =>
                  import("../components/dashboards/crypto/wallet.vue"),
              },
            ],
          },
          {
            path: "crm",
            name: "CRM",
            children: [
              {
                path: "crm-dashboard",
                name: "CRM Dashboard",
                component: () =>
                  import("../components/dashboards/crm/dashboard.vue"),
              },
              {
                path: "contacts",
                name: "Contacts",
                component: () =>
                  import("../components/dashboards/crm/contacts.vue"),
              },
              {
                path: "companies",
                name: "Companies",
                component: () =>
                  import("../components/dashboards/crm/companies.vue"),
              },
              {
                path: "deals",
                name: "Deals",
                component: () =>
                  import("../components/dashboards/crm/deals.vue"),
              },
              {
                path: "leads",
                name: "Leads",
                component: () =>
                  import("../components/dashboards/crm/leads.vue"),
              },
            ],
          },
          {
            path: "projects",
            name: "Projects",
            children: [
              {
                path: "project-dashboard",
                name: "Projects Dashboard",
                component: () =>
                  import("../components/dashboards/projects/dashboard.vue"),
              },
              {
                path: "projects-list",
                name: "Project List",
                component: () =>
                  import("../components/dashboards/projects/projects-list.vue"),
              },
              {
                path: "project-overview",
                name: "Project Overview",
                component: () =>
                  import(
                    "../components/dashboards/projects/project-overview.vue"
                  ),
              },
              {
                path: "create-project",
                name: "Create Project",
                component: () =>
                  import(
                    "../components/dashboards/projects/create-project.vue"
                  ),
              },
            ],
          },
          {
            path: "hrm",
            name: "HRM",
            component: () => import("../components/dashboards/hrm.vue"),
          },
          {
            path: "courses",
            name: "Courses",
            component: () => import("../components/dashboards/courses.vue"),
          },
          {
            path: "stocks",
            name: "Stocks",
            component: () => import("../components/dashboards/stocks.vue"),
          },
          {
            path: "nft",
            name: "NFT",
            children: [
              {
                path: "nft-dashboard",
                name: "Nft Dashboard",
                component: () =>
                  import("../components/dashboards/nft/dashboard.vue"),
              },
              {
                path: "market-place",
                name: "Market Place",
                component: () =>
                  import("../components/dashboards/nft/market-place.vue"),
              },
              {
                path: "nft-details",
                name: "NFT Details",
                component: () =>
                  import("../components/dashboards/nft/nft-details.vue"),
              },
              {
                path: "create-nft",
                name: "Create NFT",
                component: () =>
                  import("../components/dashboards/nft/create-nft.vue"),
              },
              {
                path: "wallet-integration",
                name: "Wallet ntegration",
                component: () =>
                  import("../components/dashboards/nft/wallet-integration.vue"),
              },
              {
                path: "live-auction",
                name: "Live Auction",
                component: () =>
                  import("../components/dashboards/nft/live-auction.vue"),
              },
            ],
          },
          {
            path: "jobs",
            name: "Jobs",
            children: [
              {
                path: "jobs-dashboard",
                name: "Jobs Dashboard",
                component: () =>
                  import("../components/dashboards/jobs/dashboard.vue"),
              },
              {
                path: "job-details",
                name: "Job Details",
                component: () =>
                  import("../components/dashboards/jobs/job-details.vue"),
              },
              {
                path: "search-company",
                name: "Search Company",
                component: () =>
                  import("../components/dashboards/jobs/search-company.vue"),
              },
              {
                path: "search-jobs",
                name: "Search Jobs",
                component: () =>
                  import("../components/dashboards/jobs/search-jobs.vue"),
              },
              {
                path: "job-post",
                name: "Job Post",
                component: () =>
                  import("../components/dashboards/jobs/job-post.vue"),
              },
              {
                path: "jobs-list",
                name: "Jobs List",
                component: () =>
                  import("../components/dashboards/jobs/jobs-list.vue"),
              },
              {
                path: "search-candidate",
                name: "Search Candidate",
                component: () =>
                  import("../components/dashboards/jobs/search-candidate.vue"),
              },
              {
                path: "candidate-details",
                name: "Candidate Details",
                component: () =>
                  import("../components/dashboards/jobs/candidate-details.vue"),
              },
            ],
          },
          {
            path: "podcast",
            name: "Podcast",
            component: () => import("../components/dashboards/podcast.vue"),
          },
          {
            path: "social-media",
            name: "Social Media",
            component: () =>
              import("../components/dashboards/social-media.vue"),
          },
          {
            path: "school",
            name: "School",
            component: () => import("../components/dashboards/school.vue"),
          },
          {
            path: "medical",
            name: "Medical",
            component: () => import("../components/dashboards/medical.vue"),
          },
          {
            path: "pos-system",
            name: "POS System",
            component: () => import("../components/dashboards/pos-system.vue"),
          },
        ],
      },

      //applications
      {
        path: `applications`,
        name: "Applications",
        children: [
          {
            path: "chat",
            name: "Chat",
            component: () => import("../components/applications/chat.vue"),
          },
          {
            path: "email",
            name: "Email",
            children: [
              {
                path: "mail-app",
                name: "Mail App",
                component: () =>
                  import("../components/applications/email/mail-app.vue"),
              },
              {
                path: "mail-settings",
                name: "Mail Settings",
                component: () =>
                  import("../components/applications/email/mail-settings.vue"),
              },
            ],
          },
          {
            path: "file-manager",
            name: "File Manager",
            component: () =>
              import("../components/applications/file-manager.vue"),
          },
          {
            path: "full-calendar",
            name: "Full Calendar",
            component: () =>
              import("../components/applications/full-calendar.vue"),
          },
          {
            path: "gallery",
            name: "Gallery",
            component: () => import("../components/applications/gallery.vue"),
          },
          {
            path: "sweet-alerts",
            name: "Sweet Alerts",
            component: () =>
              import("../components/applications/sweet-alerts.vue"),
          },
          {
            path: "task",
            name: "Task",
            children: [
              {
                path: "kanban-board",
                name: "Kanban Board",
                component: () =>
                  import("../components/applications/task/kanban-board.vue"),
              },
              {
                path: "list-view",
                name: "List View",
                component: () =>
                  import("../components/applications/task/list-view.vue"),
              },
            ],
          },
          {
            path: "to-do-list",
            name: "To Do List",
            component: () =>
              import("../components/applications/to-do-list.vue"),
          },
        ],
      },

      //Pages
      {
        path: `pages`,
        name: "Pages",
        children: [
          {
            path: "blog",
            name: "Blog",
            children: [
              {
                path: "blog",
                component: () => import("../components/pages/blog/blog.vue"),
              },
              {
                path: "blog-details",
                component: () =>
                  import("../components/pages/blog/blog-details.vue"),
              },
              {
                path: "create-blog",
                component: () =>
                  import("../components/pages/blog/create-blog.vue"),
              },
            ],
          },
          {
            path: "empty",
            name: "Empty",
            component: () => import("../components/pages/empty.vue"),
          },
          // Forms
          {
            path: `forms`,
            name: "Forms",
            children: [
              {
                path: "form-advanced",
                name: "Form Advanced",
                component: () =>
                  import("../components/pages/forms/form-advanced.vue"),
              },
              {
                path: "form-elements",
                name: "Form Elements",
                children: [
                  {
                    path: "inputs",
                    component: () =>
                      import(
                        "../components/pages/forms/form-elements/inputs.vue"
                      ),
                  },
                  {
                    path: "checks-radios",
                    component: () =>
                      import(
                        "../components/pages/forms/form-elements/checks-radios.vue"
                      ),
                  },
                  {
                    path: "input-group",
                    component: () =>
                      import(
                        "../components/pages/forms/form-elements/input-group.vue"
                      ),
                  },
                  {
                    path: "form-select",
                    component: () =>
                      import(
                        "../components/pages/forms/form-elements/form-select.vue"
                      ),
                  },
                  {
                    path: "range-slider",
                    component: () =>
                      import(
                        "../components/pages/forms/form-elements/range-slider.vue"
                      ),
                  },
                  {
                    path: "input-masks",
                    component: () =>
                      import(
                        "../components/pages/forms/form-elements/input-masks.vue"
                      ),
                  },
                  {
                    path: "file-uploads",
                    component: () =>
                      import(
                        "../components/pages/forms/form-elements/file-uploads.vue"
                      ),
                  },
                  {
                    path: "date-time-picker",
                    component: () =>
                      import(
                        "../components/pages/forms/form-elements/date-time-picker.vue"
                      ),
                  },
                  {
                    path: "color-picker",
                    component: () =>
                      import(
                        "../components/pages/forms/form-elements/color-picker.vue"
                      ),
                  },
                ],
              },
              {
                path: "floating-labels",
                name: "Floating Labels",
                component: () =>
                  import("../components/pages/forms/floating-labels.vue"),
              },
              {
                path: "form-layouts",
                name: "Form Layouts",
                component: () =>
                  import("../components/pages/forms/form-layouts.vue"),
              },
              {
                path: "form-wizards",
                name: "Form Wizards",
                component: () =>
                  import("../components/pages/forms/form-wizards.vue"),
              },
              {
                path: "vue-editor",
                name: "Vue Editor",
                component: () =>
                  import("../components/pages/forms/vue-editor.vue"),
              },
              {
                path: "validation",
                name: "Validation",
                component: () =>
                  import("../components/pages/forms/validation.vue"),
              },
              {
                path: "select2",
                name: "Select2",
                component: () =>
                  import("../components/pages/forms/select2.vue"),
              },
            ],
          },
          {
            path: "faqs",
            name: "Faqs",
            component: () => import("../components/pages/faqs.vue"),
          },
          {
            path: "invoice",
            name: "Invoice",
            children: [
              {
                path: "create-invoice",
                name: "Create Invoice",
                component: () =>
                  import("../components/pages/invoice/create-invoice.vue"),
              },
              {
                path: "invoice-details",
                name: "Invoice Details",
                component: () =>
                  import("../components/pages/invoice/invoice-details.vue"),
              },
              {
                path: "invoice-list",
                name: "Invoice List",
                component: () =>
                  import("../components/pages/invoice/invoice-list.vue"),
              },
            ],
          },
          {
            path: "pricing",
            name: "Pricing",
            component: () => import("../components/pages/pricing.vue"),
          },
          {
            path: "profile",
            name: "Profile",
            component: () => import("../components/pages/profile.vue"),
          },
          {
            path: "profile-settings",
            name: "Profile Settings",
            component: () => import("../components/pages/profile-settings.vue"),
          },
          {
            path: "testimonials",
            name: "Testimonials",
            component: () => import("../components/pages/testimonials.vue"),
          },
          {
            path: "search",
            name: "Search",
            component: () => import("../components/pages/search.vue"),
          },
          {
            path: "team",
            name: "Team",
            component: () => import("../components/pages/team.vue"),
          },
          {
            path: "terms-conditions",
            name: "Terms Conditions",
            component: () => import("../components/pages/terms-conditions.vue"),
          },
          {
            path: "timeline",
            name: "Timeline",
            component: () => import("../components/pages/timeline.vue"),
          },
        ],
      },
      {
        path: "general",
        name: "General",
        children: [
          //Ui Elements
          {
            path: `ui-elements`,
            name: "Ui Elements",
            children: [
              {
                path: "alerts",
                name: "Alerts",
                component: () =>
                  import("../components/general/ui-elements/alerts.vue"),
              },
              {
                path: "badge",
                name: "Badges",
                component: () =>
                  import("../components/general/ui-elements/badge.vue"),
              },
              {
                path: "breadcrumb",
                name: "Breadcrumb",
                component: () =>
                  import("../components/general/ui-elements/breadcrumb.vue"),
              },
              {
                path: "buttons",
                name: "Buttons",
                component: () =>
                  import("../components/general/ui-elements/buttons.vue"),
              },
              {
                path: "button-group",
                name: "Button Group",
                component: () =>
                  import("../components/general/ui-elements/button-group.vue"),
              },
              {
                path: "cards",
                name: "Cards",
                component: () =>
                  import("../components/general/ui-elements/cards.vue"),
              },
              {
                path: "dropdowns",
                name: "Dropdowns",
                component: () =>
                  import("../components/general/ui-elements/dropdowns.vue"),
              },
              {
                path: "images-figures",
                name: "Images & Figures",
                component: () =>
                  import(
                    "../components/general/ui-elements/images-figures.vue"
                  ),
              },
              {
                path: "links-interactions",
                name: "Links & Interactions",
                component: () =>
                  import(
                    "../components/general/ui-elements/links-interactions.vue"
                  ),
              },
              {
                path: "list-group",
                name: "List Group",
                component: () =>
                  import("../components/general/ui-elements/list-group.vue"),
              },
              {
                path: "navs-tabs",
                name: "Navs Tabs",
                component: () =>
                  import("../components/general/ui-elements/navs-tabs.vue"),
              },
              {
                path: "object-fit",
                name: "Object Fit",
                component: () =>
                  import("../components/general/ui-elements/object-fit.vue"),
              },
              {
                path: "pagination",
                name: "Pagination",
                component: () =>
                  import("../components/general/ui-elements/pagination.vue"),
              },
              {
                path: "popovers",
                name: "Popovers",
                component: () =>
                  import("../components/general/ui-elements/popovers.vue"),
              },
              {
                path: "progress",
                name: "Progress",
                component: () =>
                  import("../components/general/ui-elements/progress.vue"),
              },
              {
                path: "spinners",
                name: "Spinners",
                component: () =>
                  import("../components/general/ui-elements/spinners.vue"),
              },
              {
                path: "toasts",
                name: "Toasts",
                component: () =>
                  import("../components/general/ui-elements/toasts.vue"),
              },
              {
                path: "tooltips",
                name: "Tooltips",
                component: () =>
                  import("../components/general/ui-elements/tooltips.vue"),
              },
              {
                path: "typography",
                name: "Typography",
                component: () =>
                  import("../components/general/ui-elements/typography.vue"),
              },
            ],
          },
          //Utilities
          {
            path: `utilities`,
            name: "Utilities",
            children: [
              {
                path: "avatars",
                name: "Avatars",
                component: () =>
                  import("../components/general/utilities/avatars.vue"),
              },
              {
                path: "borders",
                name: "Borders",
                component: () =>
                  import("../components/general/utilities/borders.vue"),
              },
              {
                path: "breakpoints",
                name: "Breakpoints",
                component: () =>
                  import("../components/general/utilities/breakpoints.vue"),
              },
              {
                path: "colors",
                name: "Colors",
                component: () =>
                  import("../components/general/utilities/colors.vue"),
              },
              {
                path: "columns",
                name: "Columns",
                component: () =>
                  import("../components/general/utilities/columns.vue"),
              },
              {
                path: "css-grid",
                name: "Css Grid",
                component: () =>
                  import("../components/general/utilities/css-grid.vue"),
              },
              {
                path: "flex",
                name: "Flex",
                component: () =>
                  import("../components/general/utilities/flex.vue"),
              },
              {
                path: "gutters",
                name: "Gutter",
                component: () =>
                  import("../components/general/utilities/gutters.vue"),
              },
              {
                path: "helpers",
                name: "Helpers",
                component: () =>
                  import("../components/general/utilities/helpers.vue"),
              },
              {
                path: "position",
                name: "Position",
                component: () =>
                  import("../components/general/utilities/position.vue"),
              },
              {
                path: "additional-content",
                name: "Additional Content",
                component: () =>
                  import(
                    "../components/general/utilities/additional-content.vue"
                  ),
              },
            ],
          },
          // Advance Ui
          {
            path: `advanced-ui`,
            name: "Advanced Ui",
            children: [
              {
                path: "accordions-collapse",
                name: "Accordion Collapse",
                component: () =>
                  import(
                    "../components/general/advanced-ui/accordions-collapse.vue"
                  ),
              },
              {
                path: "carousel",
                name: "Carousel",
                component: () =>
                  import("../components/general/advanced-ui/carousel.vue"),
              },
              {
                path: "draggable-cards",
                name: "Draggable Cards",
                component: () =>
                  import(
                    "../components/general/advanced-ui/draggable-cards.vue"
                  ),
              },
              {
                path: "media-player",
                name: "Media Player",
                component: () =>
                  import("../components/general/advanced-ui/media-player.vue"),
              },
              {
                path: "modals-closes",
                name: "Modals Closes",
                component: () =>
                  import("../components/general/advanced-ui/modals-closes.vue"),
              },
              {
                path: "navbar",
                name: "Navbar",
                component: () =>
                  import("../components/general/advanced-ui/navbar.vue"),
              },
              {
                path: "offcanvas",
                name: "Offcanvas",
                component: () =>
                  import("../components/general/advanced-ui/offcanvas.vue"),
              },
              {
                path: "placeholders",
                name: "Placeholders",
                component: () =>
                  import("../components/general/advanced-ui/placeholders.vue"),
              },
              {
                path: "ratings",
                name: "Ratings",
                component: () =>
                  import("../components/general/advanced-ui/ratings.vue"),
              },
              {
                path: "ribbons",
                name: "Ribbons",
                component: () =>
                  import("../components/general/advanced-ui/ribbons.vue"),
              },
              {
                path: "sortable-js",
                name: "SortableJs",
                component: () =>
                  import("../components/general/advanced-ui/sortable-js.vue"),
              },
              {
                path: "swiper-js",
                name: "Swiper-js",
                component: () =>
                  import("../components/general/advanced-ui/swiper-js.vue"),
              },
              {
                path: "tour",
                name: "Tour",
                component: () =>
                  import("../components/general/advanced-ui/tour.vue"),
              },
            ],
          },
        ],
      },

      //widgets
      {
        path: "widgets",
        name: "Widgets",
        component: () => import("../components/widgets.vue"),
      },

      //maps
      {
        path: `maps`,
        name: "Maps",
        children: [
          {
            path: "google",
            name: "Google",
            component: () => import("../components/maps/google.vue"),
          },
          {
            path: "leaflet",
            name: "Leaflet",
            component: () => import("../components/maps/leaflet.vue"),
          },
          {
            path: "jsvector",
            name: "Jsvector",
            component: () => import("../components/maps/jsvector.vue"),
          },
        ],
      },

      //icons
      {
        path: "icons",
        name: "Icons",
        component: () => import("../components/icons.vue"),
      },

      //charts
      {
        path: `charts`,
        name: "Charts",
        children: [
          {
            path: "apex-charts",
            name: "Apex Charts",
            children: [
              {
                path: "line-chart",
                component: () =>
                  import("../components/charts/apex-charts/line-chart.vue"),
              },
              {
                path: "area-chart",
                component: () =>
                  import("../components/charts/apex-charts/area-chart.vue"),
              },
              {
                path: "column-chart",
                component: () =>
                  import("../components/charts/apex-charts/column-chart.vue"),
              },
              {
                path: "bar-chart",
                component: () =>
                  import("../components/charts/apex-charts/bar-chart.vue"),
              },
              {
                path: "mixed-chart",
                component: () =>
                  import("../components/charts/apex-charts/mixed-chart.vue"),
              },
              {
                path: "funnel-chart",
                component: () =>
                  import("../components/charts/apex-charts/funnel-chart.vue"),
              },
              {
                path: "candlestick-chart",
                component: () =>
                  import(
                    "../components/charts/apex-charts/candlestick-chart.vue"
                  ),
              },
              {
                path: "boxplot-chart",
                component: () =>
                  import("../components/charts/apex-charts/boxplot-chart.vue"),
              },
              {
                path: "bubble-chart",
                component: () =>
                  import("../components/charts/apex-charts/bubble-chart.vue"),
              },
              {
                path: "scatter-chart",
                component: () =>
                  import("../components/charts/apex-charts/scatter-chart.vue"),
              },
              {
                path: "heatmap-chart",
                component: () =>
                  import("../components/charts/apex-charts/heatmap-chart.vue"),
              },
              {
                path: "treemap-chart",
                component: () =>
                  import("../components/charts/apex-charts/treemap-chart.vue"),
              },
              {
                path: "pie-chart",
                component: () =>
                  import("../components/charts/apex-charts/pie-chart.vue"),
              },
              {
                path: "radialbar-chart",
                component: () =>
                  import(
                    "../components/charts/apex-charts/radialbar-chart.vue"
                  ),
              },
              {
                path: "radar-chart",
                component: () =>
                  import("../components/charts/apex-charts/radar-chart.vue"),
              },
              {
                path: "polararea-chart",
                component: () =>
                  import(
                    "../components/charts/apex-charts/polararea-chart.vue"
                  ),
              },
            ],
          },
          {
            path: "chartjs-charts",
            name: "ChartJs",
            component: () => import("../components/charts/chartjs-charts.vue"),
          },
          {
            path: "echart-charts",
            name: "Echarts",
            component: () => import("../components/charts/echart-charts.vue"),
          },
        ],
      },

      //tables
      {
        path: `tables`,
        name: "Tables",
        children: [
          {
            path: "tables",
            name: "Table",
            component: () => import("../components/tables/tables.vue"),
          },
          {
            path: "gridjs",
            name: "Gridjs",
            component: () => import("../components/tables/girdjs.vue"),
          },
          {
            path: "data-tables",
            name: "Data-Tables",
            component: () => import("../components/tables/data-tables.vue"),
          },
        ],
      },
    ],
  },

  {
    path: `/pages`,
    component: Landingpage,
    children: [
      {
        path: "landing",
        name: "landingpage",
        component: () => import("../components/pages/landing.vue"),
      },
    ],
  },
  // Authentication
  {
    path: `/pages/authentication`,
    component: Errorpagesinfo,
    children: [
      {
        path: "coming-soon",
        component: () =>
          import("../components/pages/authentication/coming-soon.vue"),
      },
      {
        path: "create-password",
        name: "Create Password",
        children: [
          {
            path: "basic",
            component: () =>
              import(
                "../components/pages/authentication/create-password/basic.vue"
              ),
          },
          {
            path: "cover",
            component: () =>
              import(
                "../components/pages/authentication/create-password/cover.vue"
              ),
          },
        ],
      },
      {
        path: "lock-screen",
        name: "Lock Screen",
        children: [
          {
            path: "basic",
            component: () =>
              import(
                "../components/pages/authentication/lock-screen/basic.vue"
              ),
          },
          {
            path: "cover",
            component: () =>
              import(
                "../components/pages/authentication/lock-screen/cover.vue"
              ),
          },
        ],
      },
      {
        path: "reset-password",
        name: "Reset Password",
        children: [
          {
            path: "basic",
            component: () =>
              import(
                "../components/pages/authentication/reset-password/basic.vue"
              ),
          },
          {
            path: "cover",
            component: () =>
              import(
                "../components/pages/authentication/reset-password/cover.vue"
              ),
          },
        ],
      },
      {
        path: "sign-up",
        name: "Sign Up",
        children: [
          {
            path: "basic",
            component: () =>
              import("../components/pages/authentication/sign-up/basic.vue"),
          },
          {
            path: "cover",
            component: () =>
              import("../components/pages/authentication/sign-up/cover.vue"),
          },
        ],
      },
      {
        path: "sign-in",
        name: "Sign in",
        children: [
          {
            path: "basic",
            component: () =>
              import("../components/pages/authentication/sign-in/basic.vue"),
          },
          {
            path: "cover",
            component: () =>
              import("../components/pages/authentication/sign-in/cover.vue"),
          },
        ],
      },
      {
        path: "two-step-verification",
        name: "Two Step Verification",
        children: [
          {
            path: "basic",
            component: () =>
              import(
                "../components/pages/authentication/two-step-verification/basic.vue"
              ),
          },
          {
            path: "cover",
            component: () =>
              import(
                "../components/pages/authentication/two-step-verification/cover.vue"
              ),
          },
        ],
      },
      {
        path: "under-maintenance",
        component: () =>
          import("../components/pages/authentication/under-maintainance.vue"),
      },
    ],
  },
  // earrorpage
  {
    path: `/pages/error`,
    component: Errorpagesinfo,
    children: [
      {
        path: "401-error",
        component: () => import("../components/pages/error/401-error.vue"),
      },
      {
        path: "404-error",
        component: () => import("../components/pages/error/404-error.vue"),
      },
      {
        path: "500-error",
        component: () => import("../components/pages/error/500-error.vue"),
      },
    ],
  },
  {
    path: "/",
    component: Maindashboard,
    children: [
      {
        path: `users`,
        name: "users",
        component: () => import("../view/users/index.vue"),
      },
      {
        path: `users/add`,
        name: "users-add",
        component: () => import("../view/users/add/index.vue"),
      },
      {
        path: `users/edit/:id`,
        name: "users-edit",
        component: () => import("../view/users/edit/index.vue"),
      },
      {
        path: `profile`,
        name: "profile",
        component: () => import("../view/profile/index.vue"),
      },
      {
        path: `prodi`,
        name: "prodi",
        component: () => import("../view/prodi/index.vue"),
      },
      {
        path: `prodi/add`,
        name: "prodi-add",
        component: () => import("../view/prodi/add/index.vue"),
      },
      {
        path: `prodi/edit/:id`,
        name: "prodi-edit",
        component: () => import("../view/prodi/edit/index.vue"),
      },
      {
        path: `fakultas`,
        name: "fakultas",
        component: () => import("../view/fakultas/index.vue"),
      },
      {
        path: `fakultas/add`,
        name: "fakultas-add",
        component: () => import("../view/fakultas/add/index.vue"),
      },
      {
        path: `fakultas/edit/:id`,
        name: "fakultas-edit",
        component: () => import("../view/fakultas/edit/index.vue"),
      },
      {
        path: `template-ijazah`,
        name: "template-ijazah",
        component: () => import("../view/template_ijazah/index.vue"),
      },
      {
        path: `template-ijazah/add`,
        name: "template-ijazah-add",
        component: () => import("../view/template_ijazah/add/index.vue"),
      },
      {
        path: `template-ijazah/edit/:id`,
        name: "template-ijazah-edit",
        component: () => import("../view/template_ijazah/edit/index.vue"),
      },
      {
        path: `template-ijazah/editor/:id`,
        name: "template-ijazah-editor",
        component: () => import("../view/template_ijazah/editor/index.vue"),
      },
      {
        path: `mahasiswa`,
        name: "mahasiswa",
        component: () => import("../view/mahasiswa/index.vue"),
      },
      {
        path: `mahasiswa/add`,
        name: "mahasiswa-add",
        component: () => import("../view/mahasiswa/add/index.vue"),
      },
      {
        path: `mahasiswa/edit/:id`,
        name: "mahasiswa-edit",
        component: () => import("../view/mahasiswa/edit/index.vue"),
      },
      {
        path: `sklmk`,
        name: "sklmk",
        component: () =>
          import("../view/surat_keterangan_mata_kuliah/index.vue"),
      },
      {
        path: `sklmk/add`,
        name: "sklmk-add",
        component: () =>
          import("../view/surat_keterangan_mata_kuliah/add/index.vue"),
      },
      {
        path: `sklmk/edit/:id`,
        name: "sklmk-edit",
        component: () =>
          import("../view/surat_keterangan_mata_kuliah/edit/index.vue"),
      },
      {
        path: `skukd`,
        name: "skukd",
        component: () =>
          import("../view/surat_keterangan_ujian_komprehensif/index.vue"),
      },
      {
        path: `skukd/add`,
        name: "skukd-add",
        component: () =>
          import("../view/surat_keterangan_ujian_komprehensif/add/index.vue"),
      },
      {
        path: `skukd/edit/:id`,
        name: "skukd-edit",
        component: () =>
          import("../view/surat_keterangan_ujian_komprehensif/edit/index.vue"),
      },
      {
        path: `skak`,
        name: "skak",
        component: () =>
          import("../view/surat_keterangan_administrasi_keuangan/index.vue"),
      },
      {
        path: `skak/add`,
        name: "skak-add",
        component: () =>
          import(
            "../view/surat_keterangan_administrasi_keuangan/add/index.vue"
          ),
      },
      {
        path: `skak/edit/:id`,
        name: "skak-edit",
        component: () =>
          import(
            "../view/surat_keterangan_administrasi_keuangan/edit/index.vue"
          ),
      },
      {
        path: `sktkp`,
        name: "sktkp",
        component: () =>
          import("../view/surat_keterangan_tasma_kkn_ppl/index.vue"),
      },
      {
        path: `sktkp/add`,
        name: "sktkp-add",
        component: () =>
          import("../view/surat_keterangan_tasma_kkn_ppl/add/index.vue"),
      },
      {
        path: `sktkp/edit/:id`,
        name: "sktkp-edit",
        component: () =>
          import("../view/surat_keterangan_tasma_kkn_ppl/edit/index.vue"),
      },
      {
        path: `skqa`,
        name: "skqa",
        component: () =>
          import("../view/surat_keterangan_qismul_aman/index.vue"),
      },
      {
        path: `skqa/add`,
        name: "skqa-add",
        component: () =>
          import("../view/surat_keterangan_qismul_aman/add/index.vue"),
      },
      {
        path: `skqa/edit/:id`,
        name: "skqa-edit",
        component: () =>
          import("../view/surat_keterangan_qismul_aman/edit/index.vue"),
      },
      {
        path: `skam`,
        name: "skam",
        component: () =>
          import("../view/surat_keterangan_aktif_mahasiswa/index.vue"),
      },
      {
        path: `skam/add`,
        name: "skam-add",
        component: () =>
          import("../view/surat_keterangan_aktif_mahasiswa/add/index.vue"),
      },
      {
        path: `skam/edit/:id`,
        name: "skam-edit",
        component: () =>
          import("../view/surat_keterangan_aktif_mahasiswa/edit/index.vue"),
      },
      {
        path: `spm`,
        name: "spm",
        component: () =>
          import("../view/surat_keterangan_spm/index.vue"),
      },
      {
        path: `spm/add`,
        name: "spm-add",
        component: () =>
          import("../view/surat_keterangan_spm/add/index.vue"),
      },
      {
        path: `spm/edit/:id`,
        name: "spm-edit",
        component: () =>
          import("../view/surat_keterangan_spm/edit/index.vue"),
      },
      {
        path: `spvn`,
        name: "spvn",
        component: () =>
          import("../view/surat_pernyataan_verifikasi_nilai/index.vue"),
      },
      {
        path: `spvn/add`,
        name: "spvn-add",
        component: () =>
          import("../view/surat_pernyataan_verifikasi_nilai/add/index.vue"),
      },
      {
        path: `spvn/edit/:id`,
        name: "spvn-edit",
        component: () =>
          import("../view/surat_pernyataan_verifikasi_nilai/edit/index.vue"),
      },
      {
        path: `sk`,
        name: "sk",
        component: () => import("../view/surat_keterangan/index.vue"),
      },
      {
        path: `sk/add`,
        name: "sk-add",
        component: () => import("../view/surat_keterangan/add/index.vue"),
      },
      {
        path: `sk/edit/:id`,
        name: "sk-edit",
        component: () => import("../view/surat_keterangan/edit/index.vue"),
      },
      {
        path: `st`,
        name: "st",
        component: () => import("../view/surat_tugas/index.vue"),
      },
      {
        path: `st/add`,
        name: "st-add",
        component: () => import("../view/surat_tugas/add/index.vue"),
      },
      {
        path: `st/edit/:id`,
        name: "st-edit",
        component: () => import("../view/surat_tugas/edit/index.vue"),
      },
      {
        path: `skt`,
        name: "skt",
        component: () => import("../view/surat_keterangan_transfer/index.vue"),
      },
      {
        path: `skt/add`,
        name: "skt-add",
        component: () =>
          import("../view/surat_keterangan_transfer/add/index.vue"),
      },
      {
        path: `skt/edit/:id`,
        name: "skt-edit",
        component: () =>
          import("../view/surat_keterangan_transfer/edit/index.vue"),
      },
      {
        path: `skds2`,
        name: "skds2",
        component: () => import("../view/surat_keterangan_daftar_s2/index.vue"),
      },
      {
        path: `skds2/add`,
        name: "skds2-add",
        component: () =>
          import("../view/surat_keterangan_daftar_s2/add/index.vue"),
      },
      {
        path: `skds2/edit/:id`,
        name: "skds2-edit",
        component: () =>
          import("../view/surat_keterangan_daftar_s2/edit/index.vue"),
      },
      {
        path: `sip`,
        name: "sip",
        component: () => import("../view/surat_izin_penelitian/index.vue"),
      },
      {
        path: `sip/add`,
        name: "sip-add",
        component: () => import("../view/surat_izin_penelitian/add/index.vue"),
      },
      {
        path: `sip/edit/:id`,
        name: "sip-edit",
        component: () => import("../view/surat_izin_penelitian/edit/index.vue"),
      },
      {
        path: `hasil-rapat`,
        name: "hasil-rapat",
        component: () => import("../view/hasil_rapat/index.vue"),
      },
      {
        path: `hasil-rapat/add`,
        name: "hasil-rapat-add",
        component: () => import("../view/hasil_rapat/add/index.vue"),
      },
      {
        path: `hasil-rapat/edit/:id`,
        name: "hasil-rapat-edit",
        component: () => import("../view/hasil_rapat/edit/index.vue"),
      },
      {
        path: `dashboard`,
        name: "dashboard",
        component: () => import("../view/dashboard/index.vue"),
      },
      {
        path: `tanda-tangan`,
        name: "tanda-tangan",
        component: () => import("../view/tanda_tangan/index.vue"),
      },
      {
        path: `tanda-tangan/add`,
        name: "tanda-tangan-add",
        component: () => import("../view/tanda_tangan/add/index.vue"),
      },
      {
        path: `tanda-tangan/edit/:id`,
        name: "tanda-tangan-edit",
        component: () => import("../view/tanda_tangan/edit/index.vue"),
      },
      {
        path: `setting-jabatan`,
        name: "setting-jabatan",
        component: () => import("../view/setting_jabatan/index.vue"),
      },
      {
        path: `setting-jabatan/add`,
        name: "setting-jabatan-add",
        component: () => import("../view/setting_jabatan/add/index.vue"),
      },
      {
        path: `setting-jabatan/edit/:id`,
        name: "setting-jabatan-edit",
        component: () => import("../view/setting_jabatan/edit/index.vue"),
      },
      {
        path: `skk`,
        name: "skk",
        component: () => import("../view/surat_keterangan_kkn/index.vue"),
      },
      {
        path: `skk/add`,
        name: "skk-add",
        component: () => import("../view/surat_keterangan_kkn/add/index.vue"),
      },
      {
        path: `skk/edit/:id`,
        name: "skk-edit",
        component: () => import("../view/surat_keterangan_kkn/edit/index.vue"),
      },
      {
        path: `skp`,
        name: "skp",
        component: () => import("../view/surat_keterangan_ppl/index.vue"),
      },
      {
        path: `skp/add`,
        name: "skp-add",
        component: () => import("../view/surat_keterangan_ppl/add/index.vue"),
      },
      {
        path: `skp/edit/:id`,
        name: "skp-edit",
        component: () => import("../view/surat_keterangan_ppl/edit/index.vue"),
      },
      {
        path: `batch`,
        name: "batch",
        component: () => import("../view/batch/index.vue"),
      },
      {
        path: `batch/add`,
        name: "batch-add",
        component: () => import("../view/batch/add/index.vue"),
      },
      {
        path: `batch/edit/:id`,
        name: "batch-edit",
        component: () => import("../view/batch/edit/index.vue"),
      },
      {
        path: `jenis-surat`,
        name: "jenis-surat",
        component: () => import("../view/jenis_surat/index.vue"),
      },
      {
        path: `jenis-surat/add`,
        name: "jenis-surat-add",
        component: () => import("../view/jenis_surat/add/index.vue"),
      },
      {
        path: `jenis-surat/edit/:id`,
        name: "jenis-surat-edit",
        component: () => import("../view/jenis_surat/edit/index.vue"),
      },
      {
        path: `sk6`,
        name: "sk6",
        component: () => import("../view/sk_6/index.vue"),
      },
      {
        path: `sk6/add`,
        name: "sk6-add",
        component: () => import("../view/sk_6/add/index.vue"),
      },
      {
        path: `sk6/edit/:id`,
        name: "sk6-edit",
        component: () => import("../view/sk_6/edit/index.vue"),
      },
    ],
  },
];

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
});

export default router;
