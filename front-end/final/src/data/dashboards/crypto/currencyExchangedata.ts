import Ethereum from '/images/crypto-currencies/square-color/Ethereum.svg';
import Bitcoin from '/images/crypto-currencies/square-color/Bitcoin.svg';
import Dash from '/images/crypto-currencies/square-color/Dash.svg';
import Litecoin from '/images/crypto-currencies/square-color/Litecoin.svg';
import Ripple from '/images/crypto-currencies/square-color/Ripple.svg';
import Golem from '/images/crypto-currencies/square-color/Golem.svg';
import Monero from '/images/crypto-currencies/square-color/Monero.svg';
import EOS from '/images/crypto-currencies/square-color/EOS.svg';


function randomizeArray<T>(arg: T[]): T[] {
  const array = arg.slice();
  let currentIndex = array.length;
  let temporaryValue: T, randomIndex: number;

  while (currentIndex !== 0) {
    randomIndex = Math.floor(Math.random() * currentIndex);
    currentIndex--;

    temporaryValue = array[currentIndex];
    array[currentIndex] = array[randomIndex];
    array[randomIndex] = temporaryValue;
  }

  return array;
}

var sparklineData = [47, 45, 54, 38, 56, 24, 65, 31, 37, 39, 62, 51, 35, 41, 35, 27, 93, 53, 61, 27, 54, 43, 19, 46];


const currencySeries = [
  {
    name: "Value",
    data: randomizeArray(sparklineData)
  },
]

interface currencyOptionsProps {
  color: string
}

const currencyOptions = ({ color }: currencyOptionsProps) => ({
  chart: {
    type: "area",
    height: 60,
    sparkline: {
      enabled: true,
    },
    dropShadow: {
      enabled: true,
      enabledOnSeries: undefined,
      top: 0,
      left: 0,
      blur: 1,
      color: "#fff",
      opacity: 0.05,
    },
  },
  stroke: {
    show: true,
    curve: "smooth",
    lineCap: "butt",
    colors: undefined,
    width: 1,
    dashArray: 0,
  },
  fill: {
    gradient: {
      enabled: false,
    },
  },
  yaxis: {
    min: 0,
    show: false,
  },
  xaxis: {
    axisBorder: {
      show: false,
    },
  },
  colors: [color],
  tooltip: {
    enabled: false,
  },
})



interface CurrencyCardType {
  id: string;
  imgSrc: string;
  title: string;
  percent: string;
  inc: string;
  hcount: string;
  value: string;
  price: string;
  color: string;
  height: string;
  chartOptions: object;
  chartSeries: { name: string; data: number[] }[];
}


export const bitciondata: string[] = [
  'Bitcoin',
  'Etherium',
  'Litecoin',
  'Ripple',
  'Cardano',
  'Neo',
  'Stellar',
  'EOS',
  'NEM',
], usdData: string[] = [
  'USD',
  'Pound',
  'Rupee',
  'Euro',
  'Won',
  'Dinar',
  'Rial',
],
  currencyCards: CurrencyCardType[] = [
    {
      id: "btc-currency-chart",
      imgSrc: Bitcoin,
      title: "Bitcoin - BTC",
      percent: "24.3%",
      inc: "+0.59",
      hcount: "24H",
      value: "0.00434",
      price: "$30.29",
      color: "primary",
      height: '60',
      chartOptions: currencyOptions({ color: 'rgba(152, 95, 253, 0.5)' }),
      chartSeries: currencySeries
    },
    {
      id: "eth-currency-chart",
      imgSrc: Ethereum,
      title: "Etherium - ETH",
      percent: "17.67%",
      inc: "+1.30",
      hcount: "",
      value: "1.2923",
      price: "$2,283.73",
      color: "secondary",
      height: '60',
      chartOptions: currencyOptions({ color: 'rgba(255, 73, 205,0.5)' }),
      chartSeries: currencySeries
    },
    {
      id: "dash-currency-chart",
      imgSrc: Dash,
      title: "Dash - DASH",
      percent: "17.67%",
      inc: "+1.30",
      hcount: "",
      value: "1.2923",
      price: "$2,283.73",
      color: "success",
      height: '60',
      chartOptions: currencyOptions({ color: 'rgba(253, 175, 34,0.5)' }),
      chartSeries: currencySeries
    },
    {
      id: "ltc-currency-chart",
      imgSrc: Litecoin,
      title: "Litecoin - LTC",
      percent: "17.67%",
      inc: "+1.30",
      hcount: "",
      value: "1.2923",
      price: "$2,283.73",
      color: "warning",
      height: '60',
      chartOptions: currencyOptions({ color: 'rgba(0, 201, 255, 0.5)' }),
      chartSeries: currencySeries
    },
    {
      id: "xrs-currency-chart",
      imgSrc: Ripple,
      title: "Ripple - XRS",
      percent: "17.67%",
      inc: "+1.30",
      hcount: "",
      value: "1.2923",
      price: "$2,283.73",
      color: "pink",
      height: '60',
      chartOptions: currencyOptions({ color: 'rgba(50, 212, 132, 0.5)' }),
      chartSeries: currencySeries
    },
    {
      id: "glm-currency-chart",
      imgSrc: Golem,
      title: "Golem - GLM",
      percent: "17.67%",
      inc: "+1.30",
      hcount: "",
      value: "1.2923",
      price: "$2,283.73",
      color: "purple",
      height: '60',
      chartOptions: currencyOptions({ color: 'rgba(255, 103, 87, 0.5)' }),
      chartSeries: currencySeries
    },
    {
      id: "monero-currency-chart",
      imgSrc: Monero,
      title: "Monero",
      percent: "17.67%",
      inc: "+1.30",
      hcount: "",
      value: "1.2923",
      price: "$2,283.73",
      color: "danger",
      height: '60',
      chartOptions: currencyOptions({ color: 'rgba(53, 181, 170, 0.5)' }),
      chartSeries: currencySeries
    },
    {
      id: "eos-currency-chart",
      imgSrc: EOS,
      title: "EOS",
      percent: "17.67%",
      inc: "+1.30",
      hcount: "",
      value: "1.2923",
      price: "$2,283.73",
      color: "info",
      height: '60',
      chartOptions: currencyOptions({ color: 'rgba(190, 43, 235, 0.5)' }),
      chartSeries: currencySeries
    },
  ];



