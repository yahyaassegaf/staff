import png11 from '/images/ecommerce/png/11.png';
import png12 from '/images/ecommerce/png/12.png';
import png13 from '/images/ecommerce/png/13.png';
import png14 from '/images/ecommerce/png/14.png';
import png16 from '/images/ecommerce/png/16.png';
import comppanyLogo1 from '/images/company-logos/1.png';
import comppanyLogo2 from '/images/company-logos/2.png';
import comppanyLogo3 from '/images/company-logos/3.png';
import comppanyLogo4 from '/images/company-logos/4.png';
import comppanyLogo5 from '/images/company-logos/5.png';
import comppanyLogo6 from '/images/company-logos/6.png';
import jpg1 from '/images/ecommerce/jpg/1.jpg';
import jpg2 from '/images/ecommerce/jpg/2.jpg';
import jpg3 from '/images/ecommerce/jpg/3.jpg';
import jpg4 from '/images/ecommerce/jpg/4.jpg';
import jpg5 from '/images/ecommerce/jpg/5.jpg';
import jpg6 from '/images/ecommerce/jpg/6.jpg';

interface SalesCardItemType {
  title: string;
  avatarClass: string;
  ValueClass: string;
  smallText: string;
  ValueClass1: string;
  count: string;
  percent: string;
  icon: string;
  iconColor: string;
  cardClass: string;
  priceColor: string;
  svgIcon: string;
}

interface TopSellingProductType {
  title: string;
  price: string;
  stockStatus: string;
  stockClass: string;
  sales: string;
  image: string;
}

interface DescriptionPart {
  text: string;
  class: string;
}

interface RecentActivityType {
  date: string;
  time: string;
  descriptionParts: DescriptionPart[];
  liclass?: string;
}

interface TopCustomerType {
  initials: string;
  avatarClass: string;
  name: string;
  email: string;
  spentAmount: string;
  spentClass: string;
  spentLabel: string;
}

interface UserChannelType {
  avatarClass: string;
  imgSrc: string;
  name: string;
  subtitle: string;
  arrowClass: string;
  arrowColorClass: string;
  percent: string;
  count: string;
  progressBarClass: string;
  progressBgClass: string;
  progressWidth: string;
}

interface Avatar {
  src: string;
  alt: string;
}

interface Badge {
  class: string;
  text: string;
}

interface User {
  initials: string;
  avatarClass: string;
  name: string;
  email: string;
}

interface OrderType {
  id: number;
  checked: boolean;
  orderId: string;
  user: User;
  date: string;
  time: string;
  avatars: Avatar[];
  amount: string;
  badge: Badge;
  tableClass?: string; // Optional property
}

interface Avatar {
  src: string;
  alt: string;
}

interface Status {
  text: string;
  class: string;
  icon: string;
}

interface RecentTransactionType {
  orderId: string;
  itemCount: string;
  status: Status;
  amount: string;
  date: string;
  avatars: Avatar[];
  tdClass?: string;
  count?: string;
}

export const SalesCard: SalesCardItemType[] = [
  {
    title: 'Total Revenue',
    avatarClass: 'avatar-md flex-shrink-0',
    ValueClass: 'fw-semibold lh-sm',
    smallText: 'fs-12 lh-base',
    ValueClass1: 'fs-12 lh-base',
    count: '$46,658',
    percent: '0.45%',
    icon: 'ti ti-trending-up me-1 d-inline-block fw-semibold align-middle',
    iconColor: 'success fw-medium',
    cardClass: 'dashboard-main-card overflow-hidden primary',
    priceColor: 'primary',
    svgIcon: `<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" fill="#5f6368"><path d="M18 6h-2c0-2.21-1.79-4-4-4S8 3.79 8 6H6c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zM10 10c0 .55-.45 1-1 1s-1-.45-1-1V8h2v2zm2-6c1.1 0 2 .9 2 2h-4c0-1.1.9-2 2-2zm4 6c0 .55-.45 1-1 1s-1-.45-1-1V8h2v2z" /></svg>`
  },
  {
    title: 'Refund Requests',
    avatarClass: 'avatar-md flex-shrink-0',
    ValueClass: 'fw-semibold lh-sm',
    ValueClass1: 'mb-0',
    smallText: 'fs-12 lh-base',
    count: '4,654',
    percent: '4.43%',
    icon: 'ti ti-trending-up me-1 d-inline-block fw-semibold align-middle',
    iconColor: 'success fw-medium',
    cardClass: 'dashboard-main-card overflow-hidden secondary',
    priceColor: 'secondary',
    svgIcon: `<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" fill="#5f6368"><path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1s-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm4 12h-4v3l-5-5 5-5v3h4v4z" /></svg>`
  },
  {
    title: 'Total Orders',
    avatarClass: 'avatar-md flex-shrink-0',
    ValueClass: 'fw-semibold lh-sm',
    ValueClass1: 'mb-0',
    smallText: 'fs-12 lh-base',
    count: '25,853',
    percent: '1.25%',
    icon: 'ti ti-trending-up me-1 d-inline-block fw-semibold align-middle',
    iconColor: 'success fw-medium',
    cardClass: 'dashboard-main-card overflow-hidden warning',
    priceColor: 'warning',
    svgIcon: `<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" fill="#5f6368"><path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59L5.25 14c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42l.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03L21 6.16c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21L4.27 2H1zm16 16c-1.1 0-1.99.9-1.99 2S15.9 22 17 22s2-.9 2-2-.9-2-2-2z" /></svg>`
  },
  {
    title: 'Total Visitors',
    avatarClass: 'avatar-md flex-shrink-0',
    ValueClass: 'fw-semibold lh-sm',
    ValueClass1: 'mb-0',
    smallText: 'fs-12 lh-base',
    count: '63,744',
    percent: '2.97%',
    icon: 'ti ti-trending-down me-1 d-inline-block fw-semibold align-middle',
    iconColor: 'danger fw-medium',
    cardClass: 'dashboard-main-card overflow-hidden success',
    priceColor: 'success',
    svgIcon: `<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px" viewBox="0 0 24 24" width="24px" fill="#5f6368"><g><rect fill="none" height="24" width="24"></rect></g><g><g></g><g><g><path d="M16.67,13.13C18.04,14.06,19,15.32,19,17v3h4v-3 C23,14.82,19.43,13.53,16.67,13.13z" fill-rule="evenodd"></path></g><g><circle cx="9" cy="8" fill-rule="evenodd" r="4"></circle></g><g><path d="M15,12c2.21,0,4-1.79,4-4c0-2.21-1.79-4-4-4c-0.47,0-0.91,0.1-1.33,0.24 C14.5,5.27,15,6.58,15,8s-0.5,2.73-1.33,3.76C14.09,11.9,14.53,12,15,12z" fill-rule="evenodd"></path></g><g><path d="M9,13c-2.67,0-8,1.34-8,4v3h16v-3C17,14.34,11.67,13,9,13z" fill-rule="evenodd"></path></g></g></g></svg>`
  }
],
  topSellingProducts: TopSellingProductType[] = [
    {
      title: "TaoTronics Wall Clock",
      price: "$699",
      stockStatus: "In Stock",
      stockClass: "text-success",
      sales: "1,000",
      image: png11
    },
    {
      title: "Club Fleece Hoodie",
      price: "$55",
      stockStatus: "In Stock",
      stockClass: "text-success",
      sales: "3,100",
      image: png12
    },
    {
      title: "SmartGizmo Pro Headset",
      price: "$199",
      stockStatus: "In Stock",
      stockClass: "text-success",
      sales: "1,250",
      image: png14
    },
    {
      title: "TaoTronics Cattle",
      price: "$699",
      stockStatus: "Out Of Stock",
      stockClass: "text-danger",
      sales: "1,000",
      image: png16
    },
    {
      title: "UltraMaze Ladies Bag",
      price: "$89",
      stockStatus: "In Stock",
      stockClass: "text-success",
      sales: "2,150",
      image: png13
    }
  ],
  recentActivities: RecentActivityType[] = [
    {
      date: "24,Nov",
      time: "08:45 AM",
      descriptionParts: [
        { text: "John Doe placed an order for ", class: "" },
        { text: "5x Apple iPhone 14", class: "fw-medium text-primary" }
      ]
    },
    {
      date: "24,Nov",
      time: "09:15 AM",
      descriptionParts: [
        { text: "Payment of ", class: "" },
        { text: "$1,250.00", class: "fw-medium text-default" },
        { text: " received from ", class: "" },
        { text: "Alice Smith", class: "fw-medium text-default" },
        { text: " for ", class: "" },
        { text: "Order #1020", class: "fw-medium text-warning" },
        { text: ".", class: "" }
      ]
    },
    {
      date: "24,Nov",
      time: "10:00 AM",
      descriptionParts: [
        { text: "David Brown", class: "fw-medium text-default" },
        { text: " requested a refund for ", class: "" },
        { text: "1x Samsung Galaxy S22", class: "fw-medium text-info" },
        { text: ".", class: "" }
      ]
    },
    {
      date: "24,Nov",
      time: "10:45 AM",
      descriptionParts: [
        { text: "Product ID: 5409", class: "fw-medium text-success" },
        { text: " (Sony WH-1000XM5) stock dropped below threshold.", class: "" }
      ]
    },
    {
      date: "24,Nov",
      time: "11:30 AM",
      descriptionParts: [
        { text: "Emma Johnson", class: "fw-medium text-default" },
        { text: " left a ", class: "" },
        { text: "5-star review", class: "fw-medium text-default" },
        { text: " on ", class: "" },
        { text: "Product ID: 7312", class: "fw-medium text-orange" },
        { text: " (Dell XPS 13).", class: "" }
      ],
      liclass: "mb-2"
    }
  ],
  topCustomers: TopCustomerType[] = [
    {
      initials: "JS",
      avatarClass: "avatar avatar-md bg-primary-transparent",
      name: "Jane Smith",
      email: "janesmith215@gmail.com",
      spentAmount: "$23,755",
      spentClass: "fw-semibold text-primary mb-0",
      spentLabel: "Spent"
    },
    {
      initials: "JD",
      avatarClass: "avatar avatar-md bg-secondary-transparent",
      name: "Jhon Doe",
      email: "jhondoe431@gmail.com",
      spentAmount: "$14,563",
      spentClass: "fw-semibold text-secondary mb-0",
      spentLabel: "Spent"
    },
    {
      initials: "AK",
      avatarClass: "avatar avatar-md bg-warning-transparent",
      name: "Alicia Keys",
      email: "aliciakeys986@gmail.com",
      spentAmount: "$12,075",
      spentClass: "fw-semibold text-warning mb-0",
      spentLabel: "Spent"
    },
    {
      initials: "LP",
      avatarClass: "avatar avatar-md bg-info-transparent",
      name: "Leo Phillip",
      email: "leophillip77@gmail.com",
      spentAmount: "$10,485",
      spentClass: "fw-semibold text-info mb-0",
      spentLabel: "Spent"
    },
    {
      initials: "BS",
      avatarClass: "avatar avatar-md bg-success-transparent",
      name: "Brenda Simpson",
      email: "brendasimpson075@gmail.com",
      spentAmount: "$8,533",
      spentClass: "fw-semibold text-success mb-0",
      spentLabel: "Spent"
    }
  ],
  userChannels: UserChannelType[] = [
    {
      avatarClass: "avatar avatar-md bg-light",
      imgSrc: comppanyLogo1,
      name: "CloudComm",
      subtitle: "Digital Communication",
      arrowClass: "ti ti-arrow-narrow-up",
      arrowColorClass: "text-success",
      percent: "2.98%",
      count: "3,765",
      progressBarClass: "progress-bar bg-primary",
      progressBgClass: "progress progress-xs progress-animate bg-primary-transparent",
      progressWidth: "75%"
    },
    {
      avatarClass: "avatar avatar-md bg-light",
      imgSrc: comppanyLogo2,
      name: "BuzzWave",
      subtitle: "Social Media",
      arrowClass: "ti ti-arrow-narrow-down",
      arrowColorClass: "text-danger",
      percent: "6.45%",
      count: "2,855",
      progressBarClass: "progress-bar bg-secondary",
      progressBgClass: "progress progress-xs progress-animate bg-secondary-transparent",
      progressWidth: "45%"
    },
    {
      avatarClass: "avatar avatar-md bg-light",
      imgSrc: comppanyLogo3,
      name: "NexusNet",
      subtitle: "Networking",
      arrowClass: "ti ti-arrow-narrow-up",
      arrowColorClass: "text-success",
      percent: "1.95%",
      count: "2,384",
      progressBarClass: "progress-bar bg-warning",
      progressBgClass: "progress progress-xs progress-animate bg-warning-transparent",
      progressWidth: "81%"
    },
    {
      avatarClass: "avatar avatar-md bg-light",
      imgSrc: comppanyLogo4,
      name: "FlashConnect",
      subtitle: "Direct Marketing",
      arrowClass: "ti ti-arrow-narrow-down",
      arrowColorClass: "text-danger",
      percent: "5.91%",
      count: "1,755",
      progressBarClass: "progress-bar bg-info",
      progressBgClass: "progress progress-xs progress-animate bg-info-transparent",
      progressWidth: "60%"
    },
    {
      avatarClass: "avatar avatar-md bg-light",
      imgSrc: comppanyLogo5,
      name: "EchoLink",
      subtitle: "Feedback & Surveys",
      arrowClass: "ti ti-arrow-narrow-up",
      arrowColorClass: "text-success",
      percent: "3.75%",
      count: "1,525",
      progressBarClass: "progress-bar bg-success",
      progressBgClass: "progress progress-xs progress-animate bg-success-transparent",
      progressWidth: "53%"
    },
    {
      avatarClass: "avatar avatar-md bg-light",
      imgSrc: comppanyLogo6,
      name: "VibeStream",
      subtitle: "Content Distribution",
      arrowClass: "ti ti-arrow-narrow-up",
      arrowColorClass: "text-success",
      percent: "0.95%",
      count: "1,345",
      progressBarClass: "progress-bar bg-danger",
      progressBgClass: "progress progress-xs progress-animate bg-danger-transparent",
      progressWidth: "37%"
    }
  ],
  orders: OrderType[] = [
    {
      id: 1,
      checked: true,
      orderId: "#SPK231",
      user: {
        initials: "JS",
        avatarClass: " bg-primary",
        name: "Jane Smith",
        email: "janesmith213@gmail.com",
      },
      date: "27,Aug 2024",
      time: "12:45PM",
      avatars: [
        {
          src: jpg3,
          alt: "img",
        },
        { src: jpg4, alt: "img" },
        { src: jpg5, alt: "img" },
      ],
      amount: "$1,249",
      badge: { class: "badge bg-success-transparent", text: "Paid" },
    },
    {
      id: 2,
      checked: true,
      orderId: "#SPK421",
      user: {
        initials: "JD",
        avatarClass: " bg-secondary",
        name: "Jhon Doe",
        email: "jhondoe865@gmail.com",
      },
      date: "16,Sep 2024",
      time: "11:15AM",
      avatars: [
        { src: jpg1, alt: "img" },
        { src: jpg2, alt: "img" },
      ],
      amount: "$3,299",
      badge: { class: "badge bg-warning-transparent", text: "Pending" },

    },
    {
      id: 3,
      checked: false,
      orderId: "#SPK175",
      user: {
        initials: "ED",
        avatarClass: " bg-warning",
        name: "Emiley Davis",
        email: "emileydavis234@gmail.com",
      },
      date: "15,Sep 2024",
      time: "04:45PM",
      avatars: [
        { src: jpg5, alt: "img" },
        { src: jpg6, alt: "img" },
      ],
      amount: "$4,799",
      badge: { class: "badge bg-danger-transparent", text: "Overdue" },

    },
    {
      id: 4,
      checked: true,
      orderId: "#SPK145",
      user: {
        initials: "LP",
        avatarClass: " bg-info",
        name: "Leo Phillip",
        email: "leophillip423@gmail.com",
      },
      date: "21,Sep 2024",
      time: "02:18PM",
      avatars: [{ src: jpg3, alt: "img" }],
      amount: "$2,499",
      badge: { class: "badge bg-success-transparent", text: "Paid" },

    },
    {
      id: 5,
      checked: false,
      orderId: "#SPK426",
      user: {
        initials: "SL",
        avatarClass: " bg-success",
        name: "Sara Lee",
        email: "saralee765@gmail.com",
      },
      date: "19,Oct 2024",
      time: "03:52PM",
      avatars: [
        { src: jpg4, alt: "img" },
        { src: jpg1, alt: "img" },
      ],
      amount: "$3,999",
      badge: { class: "badge bg-success-transparent", text: "Paid" },
      tableClass: 'border-bottom-0'
    },
  ],
  recentTransactions: RecentTransactionType[] = [
    {
      orderId: "#SPK1234",
      itemCount: "4 Items",
      status: {
        text: "Paid",
        class: "badge bg-success-transparent",
        icon: "ri-check-line me-1 align-middle"
      },
      amount: "$150.00",
      date: "2024-08-27",
      avatars: [
        { src: jpg1, alt: "img" },
        { src: jpg2, alt: "img" },
      ],
      count: "+2"
    },
    {
      orderId: "#SPK7432",
      itemCount: "3 Items",
      status: {
        text: "Pending",
        class: "badge bg-warning-transparent",
        icon: "ri-time-line me-1 align-middle"
      },
      amount: "$75.00",
      date: "2024-08-26",
      avatars: [
        { src: jpg3, alt: "img" },
        { src: jpg4, alt: "img" },
        { src: jpg5, alt: "img" }
      ]
    },
    {
      orderId: "#SPK3422",
      itemCount: "2 Items",
      status: {
        text: "Paid",
        class: "badge bg-success-transparent",
        icon: "ri-check-line me-1 align-middle"
      },
      amount: "$200.00",
      date: "2024-08-25",
      avatars: [
        { src: jpg6, alt: "img" },
        { src: jpg2, alt: "img" }
      ]
    },
    {
      orderId: "#SPK1578",
      itemCount: "1 Items",
      status: {
        text: "Paid",
        class: "badge bg-success-transparent",
        icon: "ri-check-line me-1 align-middle"
      },
      amount: "$120.00",
      date: "2024-08-24",
      avatars: [
        { src: jpg5, alt: "img" }
      ]
    },
    {
      orderId: "#SPK2355",
      itemCount: "5 Items",
      status: {
        text: "Failed",
        class: "badge bg-danger-transparent",
        icon: "ri-close-line me-1 align-middle"
      },
      amount: "$90.00",
      date: "2024-08-23",
      avatars: [
        { src: jpg1, alt: "img" },
        { src: jpg4, alt: "img" },
      ],
      count: "+3"
    },
    {
      orderId: "#SPK1643",
      itemCount: "1 Items",
      status: {
        text: "Paid",
        class: "badge bg-success-transparent",
        icon: "ri-check-line me-1 align-middle"
      },
      amount: "$249.00",
      date: "2024-08-16",
      avatars: [
        { src: jpg6, alt: "img" },
        { src: jpg2, alt: "img" }
      ],
      tdClass: 'border-bottom-0'
    }
  ],
  overviewoptions = {
    chart: {
      toolbar: {
        show: false
      },
      type: 'line',
      height: 365,
      // stacked: true,
    },
    grid: {
      show: true,
      xaxis: {
        lines: {
          show: true
        }
      },
      yaxis: {
        lines: {
          show: false
        }
      },
      padding: {
        top: 2,
        right: 2,
        bottom: 2,
        left: 2
      },
      borderColor: '#f1f1f1',
      strokeDashArray: 3
    },
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    dataLabels: {
      enabled: false
    },
    stroke: {
      curve: "smooth",
      width: [5, 5, 2.5],
      lineCap: "round"
    },
    legend: {
      show: true,
      position: "top",
      horizontalAlign: "left",
      markers: {
        size: 4,
        strokeWidth: 0,
      },
    },
    yaxis: {
      axisBorder: {
        show: false,
        color: "rgba(119, 119, 142, 0.05)",
        offsetX: 0,
        offsetY: 0,
      },
      axisTicks: {
        show: true,
        borderType: "solid",
        color: "rgba(119, 119, 142, 0.05)",
        width: 6,
        offsetX: 0,
        offsetY: 0,
      },
      title: {
        style: {
          color: '#adb5be',
          fontSize: '14px',
          fontFamily: 'poppins, sans-serif',
          fontWeight: 600,
          cssClass: 'apexcharts-yaxis-label',
        },
      },
      labels: {
        show: false,
        // formatter: function (y) {
        //     return y.toFixed(0) + "";
        // }
      }
    },
    xaxis: {
      type: 'month',
      axisBorder: {
        show: true,
        color: "rgba(119, 119, 142, 0.05)",
        offsetX: 0,
        offsetY: 0,
      },
      title: {
        style: {
          color: '#adb5be',
          fontSize: '5px',
          fontFamily: 'poppins, sans-serif',
          fontWeight: 600,
          cssClass: 'apexcharts-yaxis-label',
        },
      },
    },
    plotOptions: {
      bar: {
        columnWidth: "70%",
        borderRadius: 2
      }
    },

    colors: ["var(--primary-color)", 'rgb(255, 73, 205)', "rgb(50, 212, 132)"],
  },
  overviewseries = [{
    name: "Total Orders",
    type: 'bar',
    data: [74, 85, 57, 56, 76, 35, 61, 98, 36, 50, 48, 29]
  },
  {
    name: "Total Sales",
    type: 'bar',
    data: [46, 35, 101, 98, 44, 55, 57, 56, 55, 34, 79, 46]
  },
  {
    name: "Revenue",
    type: 'line',
    data: [26, 45, 41, 78, 34, 65, 27, 46, 37, 65, 49, 23]
  }],
  visitorsoptions = {
    chart: {
      height: 285,
      type: "radar",
      toolbar: {
        show: false,
      },
    },
    colors: ["var(--primary-color)", "rgb(50, 212, 132)", "rgb(253, 175, 34)"],
    stroke: {
      width: 1,
    },
    fill: {
      opacity: 0.1,
    },
    markers: {
      size: 0,
    },
    legend: {
      show: true,
      position: "bottom",
      markers: {
        size: 4,
        strokeWidth: 0,
      },
    },
    plotOptions: {
      radar: {
        size: 100,
        polygons: {
          fill: {
            colors: ['var(--primary005)', 'var(--primary005)']
          },

        }
      }
    },
    labels: ['Sun', 'Mon', 'Tues', 'Wed', 'Thu', 'Fri', 'Sat'],
    xaxis: {
      axisBorder: { show: false },
    },
    yaxis: {
      axisBorder: { show: false },
      tickAmount: 5,
    },
  },
  visitorsseries = [{
    name: 'Desktop',
    data: [80, 50, 100, 30, 40, 20, 40],
  }, {
    name: 'Mobile',
    data: [20, 30, 40, 80, 20, 90, 35],
  }, {
    name: 'Others',
    data: [40, 76, 28, 16, 8, 10, 80],
  }]






