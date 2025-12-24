import nft19 from '/images/nft-images/19.png';
import nft20 from '/images/nft-images/20.png';
import nft22 from '/images/nft-images/22.png';
import nft23 from '/images/nft-images/23.png';
import nft24 from '/images/nft-images/24.png';
import nft27 from '/images/nft-images/27.png';
import nft28 from '/images/nft-images/28.png';
import nft29 from '/images/nft-images/29.png';
import nft30 from '/images/nft-images/30.png';
import nft32 from '/images/nft-images/32.png';
import nft33 from '/images/nft-images/33.png';
import nft34 from '/images/nft-images/34.png';

interface WalletItemType {
    id: number;
    name: string;
    image: string;
}

interface WalletCardType {
    id: number;
    name: string;
    image: any;
    active?: boolean;
    avatarClass: string;
}

export const WalletList: WalletItemType[] = [
    { id: 1, name: "Etherium", image: nft34 },
    { id: 2, name: "Binance", image: nft33 },
    { id: 3, name: "Solana", image: nft32 },
    { id: 4, name: "Tezos", image: nft28 },
    { id: 5, name: "Avalanche", image: nft30 },
    { id: 6, name: "Cardano", image: nft29 },
],
    WalletCards: WalletCardType[] = [
        { id: 1, name: "MetaMask", image: nft22, active: true, avatarClass: 'p-2' },
        { id: 2, name: "Enjin Wallet", image: nft23, avatarClass: 'p-2' },
        { id: 3, name: "Trust Wallet", image: nft20, avatarClass: '' },
        { id: 4, name: "Coinbase Wallet", image: nft24, avatarClass: 'p-2' },
        { id: 5, name: "Lido", image: nft19, avatarClass: 'p-2' },
        { id: 6, name: "Huobi Wallet", image: nft27, avatarClass: '' },
    ];

