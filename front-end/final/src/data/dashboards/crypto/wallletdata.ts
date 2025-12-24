import Ethereum from '/images/crypto-currencies/square-color/Ethereum.svg';
import Bitcoin from '/images/crypto-currencies/square-color/Bitcoin.svg';
import Litecoin from '/images/crypto-currencies/square-color/Litecoin.svg';
import Ripple from '/images/crypto-currencies/square-color/Ripple.svg';
import Media68 from '/images/media/media-68.png';

interface WalletType {
  title: string;
  imgSrc: string;
  available: string;
  value: string;
  price: string;
}

interface WalletAddressType {
  id: string;
  title: string;
  walletAddress: string;
  imageSrc: string;
  received: string;
  sent: string;
  balance: string;
  currency: string;
}

export const wallets: WalletType[] = [
  {
    title: "BTC Wallet",
    imgSrc: Bitcoin,
    available: "Available BTC",
    value: "0.05437 BTC",
    price: "$1646.94 USD",
  },
  {
    title: "ETH Wallet",
    imgSrc: Ethereum,
    available: "Available ETH",
    value: "2.3892 ETH",
    price: "$4581.24 USD",
  },
  {
    title: "XRP Wallet",
    imgSrc: Ripple,
    available: "Available XRP",
    value: "234.943 XRP",
    price: "$192.29 USD",
  },
  {
    title: "LTC Wallet",
    imgSrc: Litecoin,
    available: "Available LTC",
    value: "37.254 LTC",
    price: "$3519.01 USD",
  },
],
  walletAddresses: WalletAddressType[] = [
    {
      id: "btc-wallet-address1",
      title: "BTC Wallet Address",
      walletAddress: "afb0dc8bc84625587b85415c86ef43ed8df",
      imageSrc: Media68,
      received: "6.2834",
      sent: "2.7382",
      balance: "12.5232",
      currency: "BTC",
    },
    {
      id: "btc-wallet-address2",
      title: "ETH Wallet Address",
      walletAddress: "afb0dc8bc84625587b85415c86ef43ed8df",
      imageSrc: Media68,
      received: "6.2834",
      sent: "2.7382",
      balance: "12.5232",
      currency: "ETH",
    },
    {
      id: "btc-wallet-address3",
      title: "LTC Wallet Address",
      walletAddress: "afb0dc8bc84625587b85415c86ef43ed8df",
      imageSrc: Media68,
      received: "6.2834",
      sent: "2.7382",
      balance: "12.5232",
      currency: "LTC",
    },
  ];