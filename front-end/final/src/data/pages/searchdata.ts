import book1 from "/images/media/books/1.jpg"
import book2 from "/images/media/books/2.jpg"
import book3 from "/images/media/books/3.jpg"
import book4 from "/images/media/books/4.jpg"
import book5 from "/images/media/books/5.jpg"
import book6 from "/images/media/books/6.jpg"


interface BookType {
    imgSrc: string;
    title: string;
    subTitle: string;
    date: string;
    tags: string[];
}

interface NewsItemType {
    title: string;
    description: string;
    dateTime: string;
}

interface VideoItemType {
    videoUrl: string;
    title: string;
    linkText: string;
}

//Books
export const Books: BookType[] = [
    {
        imgSrc: book6,
        title: "The Great Gatsby",
        subTitle: "F. Scott Fitzgerald",
        date: "May 10, 1925",
        tags: ["Classic", "Fiction", "Jazz Age"],
    },
    {
        imgSrc: book1,
        title: "To Kill a Mockingbird",
        subTitle: "Harper Lee",
        date: "July 11, 1960",
        tags: ["Fiction", "Legal", "Southern Gothic"],
    },
    {
        imgSrc: book2,
        title: "Nineteen Eighty-Four",
        subTitle: "George Orwell",
        date: "June 8, 1949",
        tags: ["Dystopian", "Political", "Science Fiction"],
    },
    {
        imgSrc: book3,
        title: "The Hobbit",
        subTitle: "J.R.R. Tolkien",
        date: "September 21, 1937",
        tags: ["Fantasy", "Adventure"],
    },
    {
        imgSrc: book4,
        title: "The Da Vinci Code",
        subTitle: "Dan Brown",
        date: "March 18, 2003",
        tags: ["Mystery", "Thriller", "Conspiracy"],
    },
    {
        imgSrc: book5,
        title: "Pride and Prejudice",
        subTitle: "Jane Austen",
        date: "January 28, 1813",
        tags: ["Classic", "Romance"],
    },
],
    //News
    News: NewsItemType[] = [
        {
            title: "JavaScript Frameworks: What’s New in 2025?",
            description:
                "A look at the most popular JavaScript frameworks and how they are evolving in 2025. Find out what’s driving the future of web development.",
            dateTime: "March 12, 2025 - 10:00 AM",
        },
        {
            title: "React vs Vue: Which Framework Reigns Supreme?",
            description:
                "We compare React and Vue in terms of speed, scalability, and community support. Which framework is winning the 2025 battle?",
            dateTime: "March 11, 2025 - 2:30 PM",
        },
        {
            title: "Top 5 JavaScript Frameworks for Developers in 2025",
            description:
                "Discover the best frameworks every developer should know this year, from React to Svelte. Find out why each one stands out.",
            dateTime: "March 10, 2025 - 9:15 AM",
        },
        {
            title: "What’s Next for Web Development Frameworks in 2025?",
            description:
                "Explore the future of web development frameworks and what trends developers need to keep an eye on this year.",
            dateTime: "March 9, 2025 - 1:00 PM",
        },
        {
            title: "Is Svelte the Future of Web Development?",
            description:
                "With more developers switching to Svelte, we analyze its rapid growth and why it's being touted as the future of front-end frameworks.",
            dateTime: "March 8, 2025 - 4:45 PM",
        },
    ],
    //Videos
    VideoData: VideoItemType[] = [
        {
            videoUrl: "https://www.youtube.com/embed/JP_os1DC1MQ?si=qyUJRBl7ZJBhR_P5",
            title: "YNEX - HTML Installation & Usage process",
            linkText: "https://spruko.com/demo/ynex/",

        },
        {
            videoUrl: "https://www.youtube.com/embed/-lDlbQ7DiCI?si=-GRS_5Dco6Qc5ius",
            title: "Sash - Admin and Dashboard Multipurpose HTML Advanced Template",
            linkText: "https://spruko.com/demo/sash/",

        },
        {
            videoUrl: "https://www.youtube.com/embed/HWvHA2FY8Ok?si=Bf-6pyMAcBG-_dR0",
            title: "Valex - Bootstrap 5 Admin & Dashboard HTML Template",
            linkText: "https://www.spruko.com/demo/valex/",

        },
        {
            videoUrl: "https://www.youtube.com/embed/Zx1HjMhcQdE?si=Nhbu6XA2PoyAlYhy",
            title: "Azea - Admin & Dashboard Bootstrap 5 HTML5 Template",
            linkText: "https://spruko.com/demo/azea/",

        },
        {
            videoUrl: "https://www.youtube.com/embed/prSkLfxlFDY?si=IKoJ0AUUYA9-UToN",
            title: "Uhelp - Installation Process - Helpdesk Technical Assistance Laravel Support Ticketing System",
            linkText: "https://uhelp.spruko.com/",

        },
        {
            videoUrl: "https://www.youtube.com/embed/bVRW4Li8inE?si=pOpS9ep2Hn3cGL3y",
            title: "Zanex - Bootstrap 5 Admin & Dashboard HTML5 Template",
            linkText: "https://spruko.com/demo/zanex/",

        },
        {
            videoUrl: "https://www.youtube.com/embed/HyFRnNIovUA?si=F--5ve0s5zfLuZK5",
            title: "Dayone - Bootstrap 5 HRM, Employee & Project Admin Dashboard HTML Template",
            linkText: "https://spruko.com/demo/dayone/",

        },
        {
            videoUrl: "https://www.youtube.com/embed/Yk6xPgb3cfk?si=JmFGOtV5jjFvDCbS",
            title: "Nowa - Admin and Dashboard Multipurpose HTML Advanced Template",
            linkText: "https://spruko.com/demo/nowa/",

        },
    ]