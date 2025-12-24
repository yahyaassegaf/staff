import nft21 from '/images/nft-images/21.png';
import nft25 from '/images/nft-images/25.png';
import nft26 from '/images/nft-images/26.png';
import nft31 from '/images/nft-images/31.png';
import nft36 from '/images/nft-images/36.png';
import nft38 from '/images/nft-images/38.png';
import nft40 from '/images/nft-images/40.png';
import nft41 from '/images/nft-images/41.png';
import nft42 from '/images/nft-images/42.png';
import nft43 from '/images/nft-images/43.png';

interface AcuationTagType {
    id: number;
    text: string;
    className: string;
    active?: boolean;
}

interface LiveItemType {
    id: number;
    image: string;
    title: string;
    author: string;
    price: string;
}

interface NotificationLive {
    id: number;
    image: string;
    title: string;
    time: string;
    content: string;
}

export const AcuationTags: AcuationTagType[] = [
    { id: 1, text: "Music", className: "primary", active: true },
    { id: 2, text: "Games", className: "secondary" },
    { id: 3, text: "Art", className: "info" },
    { id: 4, text: "Photography", className: "success" },
    { id: 5, text: "Sport", className: "danger" },
    { id: 6, text: "Cartoon", className: "purple" },
],
    LiveList: LiveItemType[] = [
        {
            id: 1,
            image: nft36,
            title: "Blade of the Future",
            author: "By Kairo Tetsuo",
            price: "10 ETH",
        },
        {
            id: 2,
            image: nft38,
            title: "Rebels of the Neon Streets",
            author: "By Aiko Nakamura",
            price: "5 ETH",
        },
        {
            id: 3,
            image: nft40,
            title: "The Last Cyberpunk Hero",
            author: "By Ryo Takahashi",
            price: "7 ETH",
        },
        {
            id: 4,
            image: nft41,
            title: "Reborn in the Matrix",
            author: "By Nova Cypher",
            price: "12 ETH",
        },
        {
            id: 5,
            image: nft42,
            title: "Neon Rage",
            author: "By Zara Kai",
            price: "7 ETH",
        },
        {
            id: 6,
            image: nft43,
            title: "Dawn of Darkness",
            author: "By Maxim Xeno",
            price: "8 ETH",
        },
    ],
    NotificationsLive: NotificationLive[] = [
        {
            id: 1,
            image: nft31,
            title: 'DreamSpace',
            time: '24 mins ago',
            content: `Purchsed from you by <a class="text-decoration-underline" href="javascript:void(0);">Mitchell</a> for <span class="text-success fw-medium fs-12">0.57ETH</span>.`,
        },
        {
            id: 2,
            image: nft25,
            title: 'DreamSpace',
            time: '16 mins ago',
            content: `You started following mark zensberg.`,
        },
        {
            id: 3,
            image: nft21,
            title: 'Neo Nebulae',
            time: '5 mins ago',
            content: `Showed interest in purchasing <a href="javascript:void(0);" class="fs-12 text-warning fw-medium">NeoNebulae</a>.`,
        },
        {
            id: 4,
            image: nft26,
            title: 'Neo Nebulae',
            time: '16 mins ago',
            content: `Purchased from <a href="javascript:void(0);" class="text-decoration-underline">CyberCanvas</a> for <span class="fw-medium fs-12 text-pink">1.345ETH</span>.`,
        },
    ];