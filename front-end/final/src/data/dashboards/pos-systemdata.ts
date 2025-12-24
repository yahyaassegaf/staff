import pos_system1 from "/images/pos-system/1.png"
import pos_system2 from "/images/pos-system/2.png"
import pos_system3 from "/images/pos-system/3.png"
import pos_system4 from "/images/pos-system/4.png"
import pos_system5 from "/images/pos-system/5.png"
import pos_system6 from "/images/pos-system/6.png"
import pos_system7 from "/images/pos-system/7.png"
import pos_system8 from "/images/pos-system/8.png"
import pos_system9 from "/images/pos-system/9.png"
import pos_system10 from "/images/pos-system/10.png"
import pos_system11 from "/images/pos-system/11.png"
import pos_system12 from "/images/pos-system/12.png"
import pos_system13 from "/images/pos-system/13.png"
import pos_system14 from "/images/pos-system/14.png"
import pos_system15 from "/images/pos-system/15.png"
import pos_system16 from "/images/pos-system/16.png"
import pos_system17 from "/images/pos-system/17.png"
import pos_system18 from "/images/pos-system/18.png"
import pos_system19 from "/images/pos-system/19.png"
import pos_system20 from "/images/pos-system/20.png"

interface SystemCardType {
    id: number;
    title: string;
    image: string;
    price: number;
    originalPrice: number;
    category: string;
    outOfStock?: boolean;
}

interface CartItemType {
    id: number;
    title: string;
    image: string;
    price: number;
    quantity: number;
}

interface OrderType {
    id: string;
    title: string;
    items: string;
    time: string;
    badge: string;
    color: string;
}
interface TagType {
    id: number;
    class: string;
    filter: string;
    icon: string;
    text: string;
}

export const Systemcards: SystemCardType[] = [
    {
        id: 1,
        title: "Velvety Tomato Soup",
        image: pos_system16,
        price: 14.99,
        originalPrice: 24.99,
        category: "Soups"
    },
    {
        id: 2,
        title: "Crispy Garden Medley",
        image: pos_system17,
        price: 8.49,
        originalPrice: 18.49,
        category: "Salads",
        outOfStock: true
    },
    {
        id: 3,
        title: "Frosty Dream Parfait",
        image: pos_system19,
        price: 6.99,
        originalPrice: 12.99,
        category: "Desserts"
    },
    {
        id: 4,
        title: "Zesty Caesar",
        image: pos_system18,
        price: 6.99,
        originalPrice: 14.99,
        category: "Salads"
    },
    {
        id: 5,
        title: "Citrus Breeze",
        image: pos_system11,
        price: 4.99,
        originalPrice: 6.99,
        category: "Beverages"
    },
    {
        id: 6,
        title: "Creamy Corn Bisque",
        image: pos_system15,
        price: 5.49,
        originalPrice: 12.49,
        category: "Soups"
    },
    {
        id: 7,
        title: "Cheddar Poppers",
        image: pos_system8,
        price: 7.29,
        originalPrice: 14.99,
        category: "Appetizers"
    },
    {
        id: 8,
        title: "Frosty Dream Parfait",
        image: pos_system13,
        price: 6.99,
        originalPrice: 9.99,
        category: "Desserts"
    },
    {
        id: 9,
        title: "Classic Prans Parmesan",
        image: pos_system10,
        price: 14.99,
        originalPrice: 24.99,
        category: "Main-Course"
    },
    {
        id: 10,
        title: "Sugar Rush Pie",
        image: pos_system14,
        price: 5.99,
        originalPrice: 11.99,
        category: "Desserts"
    },
    {
        id: 11,
        title: "Golden Crisps",
        image: pos_system7,
        price: 5.99,
        originalPrice: 7.99,
        category: "Appetizers"
    },
    {
        id: 12,
        title: "GrillMaster Steak",
        image: pos_system9,
        price: 18.99,
        originalPrice: 27.99,
        category: "Main-Course"
    }
],
    CartItems: CartItemType[] = [
        {
            id: 1,
            title: "Frosty Dream Parfait",
            image: pos_system13,
            price: 6.99,
            quantity: 1
        },
        {
            id: 2,
            title: "Zesty Caesar",
            image: pos_system18,
            price: 6.99,
            quantity: 1
        },
        {
            id: 3,
            title: "GrillMaster Steak",
            image: pos_system9,
            price: 18.99,
            quantity: 1
        },
        {
            id: 4,
            title: "Berry Fusion",
            image: pos_system12,
            price: 4.99,
            quantity: 1
        }
    ],
    Orders: OrderType[] = [
        {
            id: "#SPK12",
            title: "Jhon Doe",
            items: "2 Items",
            time: "12 Mins ago",
            badge: "Completed",
            color: "primary",
        },
        {
            id: "#SPK13",
            title: "Alex Turner",
            items: "3 Items",
            time: "15 Mins ago",
            badge: "Processing",
            color: "secondary",
        },
        {
            id: "#SPK14",
            title: "Rachel Adams",
            items: "1 Item",
            time: "4 Mins ago",
            badge: "Ready for Pickup",
            color: "warning",
        },
        {
            id: "#SPK15",
            title: "James Hawkins",
            items: "1 Item",
            time: "18 Mins ago",
            badge: "Out for Delivery",
            color: "success",
        },
        {
            id: "#SPK16",
            title: "Olivia Reed",
            items: "4 Items",
            time: "2 Hrs ago",
            badge: "Cancelled",
            color: "danger",
        },
        {
            id: "#SPK17",
            title: "Michael Harris",
            items: "5 Items",
            time: "8 Mins ago",
            badge: "Pending",
            color: "info",
        },
        {
            id: "#SPK18",
            title: "Sophia Miller",
            items: "1 Item",
            time: "10 Mins ago",
            badge: "Processing",
            color: "pink",
        },
    ],
    Tags: TagType[] = [
        { id: 1, class: 'primary', filter: '*', icon: pos_system20, text: 'All' },
        { id: 2, class: 'secondary', filter: '.Appetizers', icon: pos_system1, text: 'Appetizers' },
        { id: 3, class: 'info', filter: '.Main-Course', icon: pos_system2, text: 'Main Course' },
        { id: 4, class: 'success', filter: '.Beverages', icon: pos_system3, text: 'Beverages' },
        { id: 5, class: 'danger', filter: '.Desserts', icon: pos_system4, text: 'Desserts' },
        { id: 6, class: 'warning', filter: '.Soups', icon: pos_system6, text: 'Soups' },
        { id: 7, class: 'purple', filter: '.Salads', icon: pos_system5, text: 'Salads' }
    ]