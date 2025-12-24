import blog2 from '/images/media/blog/2.jpg';
import blog3 from '/images/media/blog/3.jpg';
import blog4 from '/images/media/blog/4.jpg';
import blog5 from '/images/media/blog/5.jpg';
import blog6 from '/images/media/blog/6.jpg';
import blog7 from '/images/media/blog/7.jpg';
import blog8 from '/images/media/blog/8.jpg';
import blog9 from '/images/media/blog/9.jpg';
import blog10 from '/images/media/blog/10.jpg';
import blog11 from '/images/media/blog/11.jpg';
import blog12 from '/images/media/blog/12.jpg';
import blog13 from '/images/media/blog/13.jpg';
import blog14 from '/images/media/blog/14.jpg';
import blog15 from '/images/media/blog/15.jpg';
import blog16 from '/images/media/blog/16.jpg';
import blog17 from '/images/media/blog/17.jpg';
import blog18 from '/images/media/blog/18.jpg';
import blog19 from '/images/media/blog/19.jpg';
import blog20 from '/images/media/blog/20.jpg';
import blog21 from '/images/media/blog/21.jpg';
import blog22 from '/images/media/blog/22.jpg';
import blog23 from '/images/media/blog/23.jpg';
import blog24 from '/images/media/blog/24.jpg';
import blog25 from '/images/media/blog/25.jpg';
import blog26 from '/images/media/blog/26.jpg';

interface BlogPostType {
    id: number;
    image: string;
    title: string;
    desc: string;
}

interface TopStory {
    id: number;
    image: string;
    badgeText: string;
    badgeClass: string;
    title: string;
}

interface BlogCategoryCardType {
    id: number;
    imgSrc: string;
    title: string;
}

interface BlogCardType {
    id: number;
    image: string;
    title: string;
    desc: string;
}

interface PopularBlogType {
    id: number;
    image: string;
    title: string;
    dateViews: string;
}

export const BlogPosts: BlogPostType[] = [
    {
        id: 1,
        image: blog2,
        title: "Automation",
        desc: "The Future of Automation: A Robot at Work",
    },
    {
        id: 2,
        image: blog3,
        title: "Digital Trends",
        desc: "How Platforms are Shaping Digital Communication",
    },
    {
        id: 3,
        image: blog4,
        title: "Robotics",
        desc: "The Role of Robotic Hands in Modern Technology",
    },
    {
        id: 4,
        image: blog5,
        title: "Gadgets",
        desc: "Exploring the Latest Innovations in Headphone Technology.",
    },
],
    //Top Stories
    TopStories: TopStory[] = [
        {
            id: 1,
            image: blog6,
            badgeText: "Technology & Innovation",
            badgeClass: "bg-primary-transparent",
            title: "How 5G is Revolutionizing Connectivity Across the Globe",
        },
        {
            id: 2,
            image: blog7,
            badgeText: "Health & Wellness",
            badgeClass: "bg-secondary-transparent",
            title: "The Benefits of a Plant-Based Diet: What You Need to Know",
        },
        {
            id: 3,
            image: blog8,
            badgeText: "Business & Finance",
            badgeClass: "bg-warning-transparent",
            title: "2025 Financial Trends: How to Prepare for a Changing Market",
        },
        {
            id: 4,
            image: blog9,
            badgeText: "Travel & Adventure",
            badgeClass: "bg-success-transparent",
            title: "The Future of Travel Post-Pandemic: What to Expect",
        },
        {
            id: 5,
            image: blog10,
            badgeText: "Entertainment & Culture",
            badgeClass: "bg-info-transparent",
            title: "How Social Media is Shaping the Entertainment Industry",
        },
    ],
    //Popular Topics
    BlogCategoryCards: BlogCategoryCardType[] = [
        { id: 1, imgSrc: blog11, title: "Technology" },
        { id: 2, imgSrc: blog12, title: "Health" },
        { id: 3, imgSrc: blog13, title: "Business" },
        { id: 4, imgSrc: blog14, title: "Lifestyle" },
        { id: 5, imgSrc: blog15, title: "Travel" },
        { id: 6, imgSrc: blog16, title: "Entertainment" },
        { id: 7, imgSrc: blog17, title: "Food & Recipes" },
        { id: 8, imgSrc: blog18, title: "Animals" },
    ],
    BlogCards: BlogCardType[] = [
        {
            id: 1,
            image: blog19,
            title: "Technology",
            desc: "Tech Innovations and Future Trends",
        },
        {
            id: 2,
            image: blog20,
            title: "Health & Wellness",
            desc: "How to Stay Fit and Healthy in 2025",
        },
        {
            id: 3,
            image: blog21,
            title: "Business & Finance",
            desc: "The Ultimate Guide to Personal Finance for Beginners",
        },
        {
            id: 4,
            image: blog22,
            title: "Lifestyle",
            desc: "The Art of Minimalism: Simplify Your Life",
        },
        {
            id: 5,
            image: blog23,
            title: "Productivity",
            desc: "The Secret to Effective Time Management",
        },
        {
            id: 6,
            image: blog24,
            title: "Travel",
            desc: "Top 10 Hidden Travel Gems You Need to Visit",
        },
        {
            id: 7,
            image: blog25,
            title: "Entertainment",
            desc: "Breaking Down the Latest Blockbuster Movies of 2025",
        },
        {
            id: 8,
            image: blog26,
            title: "Food & Recipes",
            desc: "Healthy and Delicious Recipes for Every Meal",
        },
    ],
    //Popular Blogs
    PopularBlogs: PopularBlogType[] = [
        {
            id: 1,
            image: blog11,
            title: "Building a Sustainable Future: How Green Technology is Changing the World",
            dateViews: "Mar 15, 2025 - 1.8k Views",
        },
        {
            id: 2,
            image: blog10,
            title: "Exploring the Rise of Remote Work: Trends and Best Practices",
            dateViews: "Apr 3, 2025 - 2.3k Views",
        },
        {
            id: 3,
            image: blog9,
            title: "Digital Marketing Trends: What You Need to Know for 2025",
            dateViews: "May 10, 2025 - 3.1k Views",
        },
        {
            id: 4,
            image: blog4,
            title: "Top 5 Budget-Friendly Home Improvement Projects",
            dateViews: "Jun 22, 2025 - 4.0k Views",
        },
        {
            id: 5,
            image: blog7,
            title: "Mastering the Art of Public Speaking",
            dateViews: "Jul 19, 2025 - 850 Views",
        },
    ];