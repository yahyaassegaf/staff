import nft2 from '/images/nft-images/2.png';
import nft3 from '/images/nft-images/3.png';
import nft4 from '/images/nft-images/4.png';
import nft6 from '/images/nft-images/6.jpg';
import nft9 from '/images/nft-images/9.jpg';
import nft11 from '/images/nft-images/11.jpg';
import nft15 from '/images/nft-images/15.jpg';
import nft16 from '/images/nft-images/16.jpg';
import nft17 from '/images/nft-images/17.jpg';
import nft8 from '/images/nft-images/8.jpg';
import nft35 from '/images/nft-images/35.png';
import nft36 from '/images/nft-images/36.png';
import nft37 from '/images/nft-images/37.png';
import nft38 from '/images/nft-images/38.png';
import nft39 from '/images/nft-images/39.png';
import nft40 from '/images/nft-images/40.png';
import nft41 from '/images/nft-images/41.png';
import nft42 from '/images/nft-images/42.png';
import nft43 from '/images/nft-images/43.png';
import face5 from '/images/faces/5.jpg';
import face10 from '/images/faces/10.jpg';
import face15 from '/images/faces/15.jpg';

export const BalanceSeries = [{
    name: 'Value',
    data: [20, 14, 19, 10, 25, 20, 22, 9, 12]
}]
export const BalanceOptions = {
    chart: {
        type: 'area',
        height: 60,
        sparkline: {
            enabled: true
        }
    },
    stroke: {
        show: true,
        curve: 'smooth',
        lineCap: 'butt',
        colors: undefined,
        width: 1.5,
        dashArray: 0,
    },
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.4,
            opacityTo: 0.1,
            stops: [0, 90, 100],
            colorStops: [
                [
                    {
                        offset: 0,
                        color: "var(--primary01)",
                        opacity: 1
                    },
                    {
                        offset: 75,
                        color: "var(--primary01)",
                        opacity: 1
                    },
                    {
                        offset: 100,
                        color: "var(--primary01)",
                        opacity: 1
                    }
                ]
            ]
        }
    },
    yaxis: {
        min: 0,
        show: false,
        axisBorder: {
            show: false
        },
    },
    xaxis: {
        show: false,
        axisBorder: {
            show: false
        },
    },
    colors: ["var(--primary-color)"],

}

export const NftSeries = [{
    name: "Last Year",
    data: [20, 38, 38, 72, 55, 63, 43, 76, 55, 80, 40, 80]
}, {
    name: "This Year",
    data: [85, 65, 75, 38, 85, 35, 62, 40, 40, 64, 50, 89]
}]
export const NftOptions = {
    chart: {
        height: 280,
        type: 'bar',
        zoom: {
            enabled: false
        },
        stacked: true,
    },
    dataLabels: {
        enabled: false
    },
    plotOptions: {
        bar: {
            columnWidth: '45%',
            borderRadius: 2,
        }
    },
    legend: {
        show: true,
        position: 'bottom',
        markers: {
            width: 10,
            height: 10,
        }
    },
    stroke: {
        curve: 'smooth',
    },
    grid: {
        borderColor: "#f1f1f1",
        strokeDashArray: 3,
    },
    colors: ["var(--primary-color)", "var(--primary02)"],
    yaxis: {
        title: {
            text: 'Statistics',
            style: {
                color: '#adb5be',
                fontSize: '14px',
                fontWeight: 600,
                cssClass: 'apexcharts-yaxis-label',
            },
        },
    },
    xaxis: {
        type: 'month',
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        axisBorder: {
            show: true,
            color: 'rgba(119, 119, 142, 0.05)',
            offsetX: 0,
            offsetY: 0,
        },
        axisTicks: {
            show: true,
            borderType: 'solid',
            color: 'rgba(119, 119, 142, 0.05)',
            width: 6,
            offsetX: 0,
            offsetY: 0
        },
        labels: {
            rotate: -90
        }
    }
}


interface NftTagType {
    id: number;
    label: string;
    className: string;
    isActive?: boolean;
}

interface NftCardType {
    id: number;
    image: string;
    title: string;
    author: string;
    likes: string;
    currentBid: string;
    bidAmount: string;
    time: string;
}

interface FollowerType {
    id: number;
    name: string;
    handle: string;
    image: string;
    isFollowing: boolean;
}

interface NftSaleType {
    id: number;
    image: string;
    title: string;
    author: string;
    price: string;
}


interface NftItemType {
    id: number;
    image: string;
    title: string;
    handle: string;
    creator: string;
    date: string;
    price: string;
    status: string;
    statusClass: string;
    category: string;
    chain: string;
    edition: string;
    volume: string;
}

interface NftImage {
    img: string;
    class: string;
}

interface NftCard {
    images: NftImage[];
    username: string;
    userusername: string;
    useravatar: string;
}

export const NftTags: NftTagType[] = [
    { id: 1, label: 'Music', className: 'primary', isActive: true },
    { id: 2, label: 'Games', className: 'secondary' },
    { id: 3, label: 'Art', className: 'info' },
    { id: 4, label: 'Photography', className: 'success' },
    { id: 5, label: 'Sport', className: 'danger' },
    { id: 6, label: 'Cartoon', className: 'purple' },
],
    NftCards: NftCardType[] = [
        {
            id: 1,
            image: nft2,
            title: 'Neon Samurai: Blade of the Future',
            author: 'By Kairo Tetsuo',
            likes: '1.32k',
            currentBid: '12.45ETH',
            bidAmount: 'Place a Bid',
            time: '04hrs : 24m : 38s'
        },
        {
            id: 2,
            image: nft3,
            title: 'Cybercity Uprising: Rebels of the Neon Streets',
            author: 'By Aiko Nakamura',
            likes: '1.26k',
            currentBid: '18.34ETH',
            bidAmount: 'Place a Bid',
            time: '04hrs : 24m : 38s'
        },
        {
            id: 3,
            image: nft4,
            title: 'Holo-Vision: The Last Cyberpunk Hero',
            author: 'By Ryo Takahashi',
            likes: '2.45k',
            currentBid: '26.50ETH',
            bidAmount: 'Place a Bid',
            time: '04hrs : 24m : 38s'
        },
    ],
    Followers: FollowerType[] = [
        {
            id: 1,
            name: 'Kairo Tetsuo',
            handle: '@KairoX',
            image: nft6,
            isFollowing: false,
        },
        {
            id: 2,
            name: 'Aiko Nakamura',
            handle: '@NamiGhost',
            image: nft15,
            isFollowing: true,
        },
        {
            id: 3,
            name: 'Kairo Tetsuo',
            handle: '@KairoX',
            image: nft16,
            isFollowing: false,
        },
        {
            id: 4,
            name: 'Ryo Takahashi',
            handle: '@TakaraVision',
            image: nft17,
            isFollowing: false,
        },
        {
            id: 5,
            name: 'Nova Cypher',
            handle: '@CypherNova',
            image: nft11,
            isFollowing: false,
        },
        {
            id: 6,
            name: 'Zara Kai',
            handle: '@ZaraKx',
            image: nft9,
            isFollowing: false,
        },
        {
            id: 7,
            name: 'Maxim Xeno',
            handle: '@XenoMax',
            image: nft8,
            isFollowing: false,
        },
    ],
    NftSales: NftSaleType[] = [
        {
            id: 1,
            image: nft36,
            title: "Blade of the Future",
            author: "Kairo Tetsuo",
            price: "10 ETH",
        },
        {
            id: 2,
            image: nft38,
            title: "Rebels of the Neon Streets",
            author: "Aiko Nakamura",
            price: "5 ETH",
        },
        {
            id: 3,
            image: nft40,
            title: "The Last Cyberpunk Hero",
            author: "Ryo Takahashi",
            price: "7 ETH",
        },
        {
            id: 4,
            image: nft41,
            title: "Reborn in the Matrix",
            author: "Nova Cypher",
            price: "12 ETH",
        },
        {
            id: 5,
            image: nft42,
            title: "Neon Rage",
            author: "Zara Kai",
            price: "7 ETH",
        },
        {
            id: 6,
            image: nft43,
            title: "Dawn of Darkness",
            author: "Maxim Xeno",
            price: "8 ETH",
        },
    ],
    NftCardsData: NftCard[] = [
        {
            images: [
                { img: nft38, class: '12' },
                { img: nft40, class: '6' },
                { img: nft41, class: '6' },
            ],
            username: "Melissa Smith",
            userusername: "@melissa",
            useravatar: face5,

        },
        {
            images: [
                { img: nft35, class: '12' },
                { img: nft36, class: '6' },
                { img: nft37, class: '6' },
            ],
            username: "Simon Cowell",
            userusername: "@simon",
            useravatar: face15,
        },
        {
            images: [
                { img: nft39, class: '12' },
                { img: nft42, class: '6' },
                { img: nft43, class: '6' },
            ],
            username: "Json Talor",
            userusername: "@taylor",
            useravatar: face10,
        },
    ],
    NftTableData: NftItemType[] = [
        {
            id: 1,
            image: nft40,
            title: "Neon Samurai",
            handle: "@KairoX",
            creator: "Kairo Tetsuo",
            date: "Feb 25, 2025",
            price: "10 ETH",
            status: "Sold Out",
            statusClass: "success",
            category: "Cyberpunk",
            chain: "Ethereum",
            edition: "1 of 1",
            volume: "50 ETH",
        },
        {
            id: 2,
            image: nft42,
            title: "Cybercity Uprising",
            handle: "@NamiGhost",
            creator: "Aiko Nakamura",
            date: "Mar 10, 2025",
            price: "5 ETH",
            status: "50% Sold",
            statusClass: "success",
            category: "Cyberpunk",
            chain: "Polygon",
            edition: "3 of 10",
            volume: "15 ETH",
        },
        {
            id: 3,
            image: nft35,
            title: "Holo-Vision Hero",
            handle: "@TakaraVision",
            creator: "Ryo Takahashi",
            date: "Mar 18, 2025",
            price: "7 ETH",
            status: "80% Sold",
            statusClass: "success",
            category: "Sci-Fi",
            chain: "Ethereum",
            edition: "5 of 5",
            volume: "20 ETH",
        },
        {
            id: 4,
            image: nft36,
            title: "Cyber Phoenix",
            handle: "@CypherNova",
            creator: "Nova Cypher",
            date: "Apr 2, 2025",
            price: "12 ETH",
            status: "10% Sold",
            statusClass: "success",
            category: "Cyberpunk",
            chain: "Binance",
            edition: "10 of 20",
            volume: "10 ETH",
        },
        {
            id: 5,
            image: nft37,
            title: "Digital Outlaws",
            handle: "@ZaraKx",
            creator: "Zara Kai",
            date: "Apr 15, 2025",
            price: "15 ETH",
            status: "Coming Soon",
            statusClass: "primary",
            category: "Cyberpunk",
            chain: "Solana",
            edition: "1 of 1",
            volume: "0 ETH",
        },
        {
            id: 6,
            image: nft38,
            title: "Cyber Reaper",
            handle: "@XenoMax",
            creator: "Maxim Xeno",
            date: "Apr 20, 2025",
            price: "8 ETH",
            status: "Pre-Sale",
            statusClass: "warning",
            category: "Cyberpunk",
            chain: "Ethereum",
            edition: "7 of 10",
            volume: "25 ETH",
        },
    ];