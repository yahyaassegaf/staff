import face1 from '/images/faces/1.jpg';
import face3 from '/images/faces/3.jpg';
import face11 from '/images/faces/11.jpg';
import face13 from '/images/faces/13.jpg';
import face15 from '/images/faces/15.jpg';
import media41 from '/images/media/media-41.jpg';
import media43 from '/images/media/media-43.jpg';
import media44 from '/images/media/media-44.jpg';
import media45 from '/images/media/media-45.jpg';
import media46 from '/images/media/media-46.jpg';
import media71 from '/images/media/media-71.jpg';
import media73 from '/images/media/media-73.jpg';
import media75 from '/images/media/media-75.jpg';
import media76 from '/images/media/media-76.jpg';
import media77 from '/images/media/media-77.jpg';
import media78 from '/images/media/media-78.jpg';
import media79 from '/images/media/media-79.jpg';

interface PodcasterType {
    id: number;
    name: string;
    avatar: string;
    title: string;
    category: string;
    followers: string;
    badgeClass: string;
}

interface MediaCardType {
    id: number;
    image: string;
    title: string;
    author: string;
}

interface RecentlyMediaItemType {
    id: number;
    image: string;
    title: string;
    author: string;
}

interface ListItemType {
    id: number | string;
    image: string;
    title: string;
    author: string;
    likes: number;
}

interface PodcastType {
    id: number;
    image: string;
    title: string;
    author: string;
    category: string;
    episodes: number;
    views: string;
    duration: string;
    comments: string;
    likes: string;
    shares: string;
    rating: number;
    status: string;
}

interface PodcastCategoryType {
    id: number;
    className: string;
    icon: string;
    text: string;
}

//Top Podcasters
export const Podcasters: PodcasterType[] = [
    {
        id: 1,
        name: 'John Doe',
        avatar: face13,
        title: 'Tech Talks',
        category: 'Technology',
        followers: '1.2M - Followers',
        badgeClass: 'primary',
    },
    {
        id: 2,
        name: 'Sarah Lee',
        avatar: face3,
        title: 'Fitness Fuel',
        category: 'Health & Fitness',
        followers: '850K - Followers',
        badgeClass: 'secondary',
    },
    {
        id: 3,
        name: 'Emily Stone',
        avatar: face1,
        title: 'True Crime Chronicles',
        category: 'True Crime',
        followers: '2.1M - Followers',
        badgeClass: 'success',
    },
    {
        id: 4,
        name: 'Mark Taylor',
        avatar: face15,
        title: 'Financial Futures',
        category: 'Finance & Economics',
        followers: '1M - Followers',
        badgeClass: 'info',
    },
    {
        id: 5,
        name: 'David Brown',
        avatar: face11,
        title: 'The Comedy Show',
        category: 'Comedy',
        followers: '1.5M - Followers',
        badgeClass: 'warning',
    },
],
    //Recommendations 
    MediaCards: MediaCardType[] = [
        {
            id: 1,
            image: media71,
            title: 'The Culture Lab',
            author: 'By Sophia Miller',
        },
        {
            id: 2,
            image: media73,
            title: 'Morning Brew',
            author: 'By Jake Matthews',
        },
        {
            id: 3,
            image: media79,
            title: 'Future of Finance',
            author: 'By Sarah Lee',
        },
        {
            id: 4,
            image: media77,
            title: 'Waves of Change',
            author: 'By David Green',
        },
        {
            id: 5,
            image: media76,
            title: 'Tech Deep Dive',
            author: 'By Emily Scott',
        },
        {
            id: 6,
            image: media75,
            title: 'Unsolved Mysteries',
            author: 'By Rachel Adams',
        },
    ],
    //Recently Played
    RecentlyMedia: RecentlyMediaItemType[] = [
        {
            id: 1,
            image: media43,
            title: "The Digital Revolution",
            author: "By Alex Turner",
        },
        {
            id: 2,
            image: media44,
            title: "Unsolved Mysteries",
            author: "By Rachel Adams",
        },
        {
            id: 3,
            image: media45,
            title: "Startup Stories",
            author: "By James Hawkins",
        },
        {
            id: 4,
            image: media46,
            title: "Healthy Habits",
            author: "By Olivia Reed",
        },
        {
            id: 5,
            image: media41,
            title: "Beyond the Horizon",
            author: "By Michael Harris",
        },
    ],
    ListItems: ListItemType[] = [
        {
            id: "01",
            image: media73,
            title: "Voices of the Future",
            author: "By John Harris",
            likes: 340,
        },
        {
            id: "02",
            image: media79,
            title: "The Art of Storytelling",
            author: "By Mia Roberts",
            likes: 133,
        },
        {
            id: "03",
            image: media77,
            title: "The Healthy Life",
            author: "By Laura Collins",
            likes: 234,
        },
        {
            id: "04",
            image: media76,
            title: "Crisis Management",
            author: "By Robert Blake",
            likes: 432,
        },
        {
            id: "05",
            image: media75,
            title: "Gastronomic Adventures",
            author: "By Lily Jackson",
            likes: 153,
        },
        {
            id: "06",
            image: media71,
            title: "Innovate to Win",
            author: "By Chris Brooks",
            likes: 355,
        },
    ],
    PodcastTable: PodcastType[] = [
        {
            id: 1,
            image: media71,
            title: "The Digital Revolution",
            author: "Alex Turner",
            category: "Technology",
            episodes: 25,
            views: "1.2M",
            duration: "35 min",
            comments: "24K",
            likes: "5K",
            shares: "1.5K",
            rating: 4.8,
            status: "Active",
        },
        {
            id: 2,
            image: media79,
            title: "Unsolved Mysteries",
            author: "Rachel Adams",
            category: "True Crime",
            episodes: 40,
            views: "3.1M",
            duration: "50 min",
            comments: "32K",
            likes: "8K",
            shares: "3.2K",
            rating: 4.9,
            status: "Active",
        },
        {
            id: 3,
            image: media78,
            title: "Startup Stories",
            author: "James Hawkins",
            category: "Business",
            episodes: 15,
            views: "850K",
            duration: "45 min",
            comments: "19K",
            likes: "3K",
            shares: "1K",
            rating: 4.7,
            status: "Inactive",
        },
        {
            id: 4,
            image: media77,
            title: "Healthy Habits",
            author: "Olivia Reed",
            category: "Health & Fitness",
            episodes: 20,
            views: "900K",
            duration: "38 min",
            comments: "22K",
            likes: "4.5K",
            shares: "1.2K",
            rating: 4.5,
            status: "Active",
        },
        {
            id: 5,
            image: media76,
            title: "Beyond the Horizon",
            author: "Michael Harris",
            category: "Travel",
            episodes: 12,
            views: "500K",
            duration: "40 min",
            comments: "15K",
            likes: "2K",
            shares: "800",
            rating: 4.6,
            status: "Active",
        },
        {
            id: 6,
            image: media75,
            title: "The Culture Lab",
            author: "Sophia Miller",
            category: "Culture",
            episodes: 30,
            views: "1.8M",
            duration: "28 min",
            comments: "27K",
            likes: "6K",
            shares: "2.5K",
            rating: 4.8,
            status: "Active",
        },
    ],
    PodcastCategeries: PodcastCategoryType[] = [
        { id: 1, className: 'primary active', icon: 'world', text: 'All', },
        { id: 2, className: 'secondary', icon: 'flame', text: 'New Trends' },
        { id: 3, className: 'info', icon: 'palette', text: 'Art Work' },
        { id: 4, className: 'success', icon: 'device-gamepad-2', text: 'Games' },
        { id: 5, className: 'danger', icon: 'tie', text: 'Fashion' },
        { id: 6, className: 'purple', icon: 'music', text: 'Music' },
    ]