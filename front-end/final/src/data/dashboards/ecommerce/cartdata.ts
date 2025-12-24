import jpg1 from '/images/ecommerce/jpg/1.jpg';
import jpg2 from '/images/ecommerce/jpg/2.jpg';
import jpg3 from '/images/ecommerce/jpg/3.jpg';
import jpg4 from '/images/ecommerce/jpg/4.jpg';
import jpg5 from '/images/ecommerce/jpg/5.jpg';

interface CartItemType {
  id: number;
  brand: string;
  name: string;
  color: string;
  size: string;
  price: number;
  quantity: number;
  stockStatus: string;
  stockStatusColor: boolean;
  image: string;
  tdClass?: string;
}

interface PricingSummaryItemType {
  label: string;
  value: string;
  isDiscount?: boolean;
  isFree?: boolean;
  stockStatusColor?: boolean;
}

export const cartData: CartItemType[] = [
  {
    id: 0,
    brand: 'Adidas',
    name: 'Adidas UltraBoost 2023',
    color: 'Green',
    size: '10 US',
    price: 159.99,
    quantity: 1,
    stockStatus: 'In Stock',
    stockStatusColor: true,
    image: jpg1,
  },
  {
    id: 1,
    brand: 'Puma',
    name: 'Puma RS-X Sneakers',
    color: 'Gray',
    size: '8 US',
    price: 119.99,
    quantity: 2,
    stockStatus: 'Limited Stock',
    stockStatusColor: false,
    image: jpg2,
  },
  {
    id: 2,
    brand: 'Reebok',
    name: 'Reebok Classic Leather',
    color: 'Blue',
    size: '11 US',
    price: 89.99,
    quantity: 1,
    stockStatus: 'In Stock',
    stockStatusColor: true,
    image: jpg3,
  },
  {
    id: 3,
    brand: 'Nike',
    name: 'Nike Air Max 2025 Sneakers',
    color: 'Teal',
    size: '10 US',
    price: 129.99,
    quantity: 1,
    stockStatus: 'In Stock',
    stockStatusColor: true,
    image: jpg4,
  },
  {
    id: 4,
    brand: 'Under Armour',
    name: 'Under Armour HOVR Phantom',
    color: 'Neon Green',
    size: '10 US',
    price: 139.99,
    quantity: 2,
    stockStatus: 'In Stock',
    stockStatusColor: true,
    image: jpg5,
    tdClass: "border-bottom-0"
  }
],
  pricingSummary: PricingSummaryItemType[] = [
    {
      label: "Sub Total",
      value: "$929.79",
    },
    {
      label: "Discount (10%)",
      value: "- $92.97",
      isDiscount: true,
      stockStatusColor: true,
    },
    {
      label: "Tax",
      value: "$0.00",
    },
    {
      label: "Shipping",
      value: "Free",
      isFree: true,
      stockStatusColor: true,
    },
    {
      label: "Total",
      value: "$837.79",
    },
  ];