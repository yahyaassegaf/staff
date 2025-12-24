import face1 from '/images/faces/1.jpg';
import face2 from '/images/faces/2.jpg';
import face3 from '/images/faces/3.jpg';
import face4 from '/images/faces/4.jpg';
import face5 from '/images/faces/5.jpg';
import face6 from '/images/faces/6.jpg';
import face9 from '/images/faces/9.jpg';
import face10 from '/images/faces/10.jpg';
import face13 from '/images/faces/13.jpg';
import face14 from '/images/faces/14.jpg';
import face15 from '/images/faces/15.jpg';

interface ReviewType {
    title: string;
    stars: ('fill' | 'half-fill' | 'line')[];
    description: string;
    name: string;
    role: string;
    imgSrc: string;
}

interface CustomReviewType {
    title: string;
    description: string;
    name: string;
    role: string;
    imgSrc: string;
    color: string;
    stars: ('fill' | 'half-fill' | 'line')[];
}

interface TestimonialType {
    id: number;
    image: string;
    name: string;
    role: string;
    stars: ('fill' | 'half-fill' | 'line')[];
    text: string;
}

interface TestimonialTypes {
    id: string;
    name: string;
    role: string;
    text: string;
    image: string;
    stars: ('fill' | 'half-fill' | 'line')[];
    cardClass: string;
}

export const Reviews: ReviewType[] = [
    {
        title: 'Quality',
        stars: ['fill', 'fill', 'fill', 'fill', 'half-fill',],
        description:
            'The product is well-designed and of great quality. It met all my expectations, and I am satisfied with my purchase.',
        name: 'John Doe',
        role: 'Product Manager',
        imgSrc: face10,
    },
    {
        title: 'Overall Experience',
        stars: ['fill', 'fill', 'fill', 'fill', 'fill',],
        description: 'The performance of this product is outstanding. It works smoothly without any lag.',
        name: 'Sarah Lee',
        role: 'Software Engineer',
        imgSrc: face2,
    },
    {
        title: 'Performance',
        stars: ['fill', 'fill', 'fill', 'fill', 'half-fill',],
        description:
            'The product is well-designed and of great quality. It met all my expectations, and I am satisfied with my purchase.',
        name: 'John Doe',
        role: 'Product Manager',
        imgSrc: face10,
    },
    {
        title: 'Usability',
        stars: ['fill', 'fill', 'fill', 'line', 'line',],
        description:
            'The usability of the product is okay, but there are some features that could be improved for easier navigation.',
        name: 'Emily Johnson',
        role: 'Marketing Lead',
        imgSrc: face4,
    },
    {
        title: 'Customer Support',
        stars: ['fill', 'fill', 'fill', 'fill', 'line',],
        description:
            'The customer support team was very responsive and helpful in resolving my queries. The issue was fixed.',
        name: 'David Smith',
        role: 'Sales Manager',
        imgSrc: face13,
    },
    {
        title: 'Value for Money',
        stars: ['fill', 'fill', 'fill', 'fill', 'half-fill',],
        description:
            'The product is decent, but I feel it’s a little overpriced for the features it offers. It’s good, but not exceptional.',
        name: 'Michael Brown',
        role: 'Business Analyst',
        imgSrc: face15,
    },
],
    //Testimonials Style 2
    CustomReviews: CustomReviewType[] = [
        {
            title: "User Experience",
            description: "The customizable templates and clean, user-friendly interface make designing creative assets a breeze. It has boosted our team’s efficiency and allowed us to meet tight deadlines with ease.",
            name: "Clara Johnson",
            role: "Senior Graphic Designer",
            imgSrc: face1,
            color: "primary border-0",
            stars: ['fill', 'fill', 'fill', 'fill', 'half-fill',],
        },
        {
            title: "Integration & Compatibility",
            description: "The integration features are excellent and have made a huge difference in streamlining our workflow. It fits perfectly with our existing tools and allows our teams to collaborate better.",
            name: "Peter Hayes",
            role: "Chief Technology Officer",
            imgSrc: face10,
            color: "success border-0",
            stars: ['fill', 'fill', 'fill', 'fill', 'half-fill',],
        },
        {
            title: "Product Quality",
            description: "This product has revolutionized our marketing strategy by providing real-time analytics and a seamless experience. We’ve seen significant improvements in our customer engagement.",
            name: "John Thompson",
            role: "Marketing Director",
            imgSrc: face9,
            color: "warning border-0",
            stars: ['fill', 'fill', 'fill', 'fill', 'half-fill',],
        },
        {
            title: "Efficiency",
            description: "The automation tools have saved us so much time, especially during critical project timelines. We've been able to reduce delays and improve our overall project delivery rates.",
            name: "Ashley Miller",
            role: "Project Manager",
            imgSrc: face5,
            color: "info border-0",
            stars: ['fill', 'fill', 'fill', 'fill', 'half-fill',],
        },
        {
            title: "Customer Support",
            description: "Amazing customer support team—always available and ready to resolve issues. Their prompt responses and dedication to fixing problems have made our experience exceptional.",
            name: "Kevin Brown",
            role: "Customer Relations Manager",
            imgSrc: face14,
            color: "danger border-0",
            stars: ['fill', 'fill', 'fill', 'fill', 'half-fill',],
        },
        {
            title: "Sales Performance",
            description: "The tools and analytics have significantly improved our sales process. I’m now able to track leads better, prioritize tasks, and close deals more efficiently. It’s truly a game-changer.",
            name: "Grace Lee",
            role: "Sales Executive",
            imgSrc: face3,
            color: "teal border-0",
            stars: ['fill', 'fill', 'fill', 'fill', 'half-fill',],
        },
    ],
    //Testimonials Style 3
    Testimonials3: TestimonialType[] = [
        {
            id: 1,
            image: face1,
            name: "Clara Johnson",
            role: "Senior Graphic Designer",
            stars: ['fill', 'fill', 'fill', 'fill', 'half-fill',],
            text: "This product has made a difference in my daily routine. Simple, effective, and worth every penny.",
        },
        {
            id: 2,
            image: face6,
            name: "Ashley Miller",
            role: "Project Manager",
            stars: ['fill', 'fill', 'fill', 'fill', 'half-fill',],
            text: "I love the modern design, and it delivers top-notch performance. A great addition to my setup!",
        },
        {
            id: 3,
            image: face3,
            name: "Grace Lee",
            role: "Sales Executive",
            stars: ['fill', 'fill', 'fill', 'fill', 'half-fill',],
            text: "Initially skeptical, but this product exceeded my expectations. Highly recommended.",
        },
        {
            id: 4,
            image: face10,
            name: "Lucas Scott",
            role: "Business Consultant",
            stars: ['fill', 'fill', 'fill', 'fill', 'half-fill',],
            text: "Does exactly what it promises. Easy to use, durable, and fantastic. I'm a happy customer!",
        },
        {
            id: 5,
            image: face5,
            name: "Maria Evans",
            role: "Operations Manager",
            stars: ['fill', 'fill', 'fill', 'fill', 'half-fill',],
            text: "Affordable and high-quality. This product outshines competitors. Thrilled with the value I got!",
        },
        {
            id: 6,
            image: face6,
            name: "Rachel Walker",
            role: "Senior Analyst",
            stars: ['fill', 'fill', 'fill', 'fill', 'half-fill',],
            text: "Exceptional product. Quick responses and a genuine commitment to customer satisfaction.",
        },
    ],
    //Testimonials Style 4
    Testimonialsdata: TestimonialTypes[] = [
        {
            id: '1',
            name: 'John Thompson',
            role: 'Marketing Director',
            text: 'This product has revolutionized our marketing strategy by providing real-time analytics and a seamless experience. We’ve seen significant improvements in our customer engagement.',
            image: face9,
            stars: ['fill', 'fill', 'fill', 'fill', 'half-fill',],
            cardClass: 'primary',
        },
        {
            id: '2',
            name: 'Clara Johnson',
            role: 'Senior Graphic Designer',
            text: 'The customizable templates and clean, user-friendly interface make designing creative assets a breeze. It has boosted our team’s efficiency and allowed us to meet tight deadlines with ease.',
            image: face2,
            stars: ['fill', 'fill', 'fill', 'fill', 'half-fill',],
            cardClass: 'success',
        },
        {
            id: '3',
            name: 'Peter Hayes',
            role: 'Chief Technology Officer',
            text: 'The integration features are excellent and have made a huge difference in streamlining our workflow. It fits perfectly with our existing tools and allows our teams to collaborate better.',
            image: face10,
            stars: ['fill', 'fill', 'fill', 'fill', 'half-fill',],
            cardClass: 'warning',
        },
        {
            id: '4',
            name: 'Ashley Miller',
            role: 'Project Manager',
            text: 'The automation tools have saved us so much time, especially during critical project timelines. We\'ve been able to reduce delays and improve our overall project delivery rates and exceptional support.',
            image: face3,
            stars: ['fill', 'fill', 'fill', 'fill', 'half-fill',],
            cardClass: 'info',
        },
        {
            id: '5',
            name: 'Kevin Brown',
            role: 'Customer Relations Manager',
            text: 'Amazing customer support team—always available and ready to resolve issues. Their prompt responses and dedication to fixing problems have made our experience exceptional.',
            image: face13,
            stars: ['fill', 'fill', 'fill', 'fill', 'half-fill',],
            cardClass: 'danger',
        },
        {
            id: '6',
            name: 'Grace Lee',
            role: 'Sales Executive',
            text: 'The tools and analytics have significantly improved our sales process. I’m now able to track leads better, prioritize tasks, and close deals more efficiently. It’s truly a game-changer.',
            image: face5,
            stars: ['fill', 'fill', 'fill', 'fill', 'half-fill',],
            cardClass: 'teal',
        },
    ]