import jpg1 from '/images/ecommerce/jpg/1.jpg'
import jpg2 from '/images/ecommerce/jpg/2.jpg'
import jpg4 from '/images/ecommerce/jpg/4.jpg'


interface CheckoutItemType {
  id: number;
  name: string;
  color: string;
  quantity: number;
  price: number;
  imageUrl: string;
}

interface SummaryDataItemType {
  label: string;
  value: string;
  isFree?: boolean;
  isTotal?: boolean;
  class?: string;
}

export const checkoutSummary: CheckoutItemType[] = [
  {
    id: 1,
    name: 'Adidas UltraBoost 2023',
    color: 'Green',
    quantity: 2,
    price: 159.99,
    imageUrl: jpg1,
  },
  {
    id: 2,
    name: 'Reebok Classic Leather',
    color: 'Blue',
    quantity: 1,
    price: 89.99,
    imageUrl: jpg2,
  },
  {
    id: 3,
    name: 'Nike Air Max 2025 Sneakers',
    color: 'Teal Blue',
    quantity: 1,
    price: 129.99,
    imageUrl: jpg4,
  },
],
  summaryData: SummaryDataItemType[] = [
    { label: 'Sub Total', value: '$929.79' },
    { label: 'Discount (10%)', value: '- $92.97' },
    { label: 'Tax', value: '$0.00' },
    { label: 'Shipping', value: 'Free', isFree: true },
    { label: 'Total', class: 'text-primary', value: '$836.82', isTotal: true },
  ];