
import face1 from '/images/faces/1.jpg';
import face2 from '/images/faces/2.jpg';
import face3 from '/images/faces/3.jpg';
import face4 from '/images/faces/4.jpg';
import face5 from '/images/faces/5.jpg';
import face6 from '/images/faces/6.jpg';
import face7 from '/images/faces/7.jpg';
import face8 from '/images/faces/8.jpg';
import face9 from '/images/faces/9.jpg';
import face10 from '/images/faces/10.jpg';
import face11 from '/images/faces/11.jpg';
import face12 from '/images/faces/12.jpg';
import face13 from '/images/faces/13.jpg';
import face14 from '/images/faces/14.jpg';
import face15 from '/images/faces/15.jpg';
import face16 from '/images/faces/16.jpg';
import face17 from '/images/faces/17.jpg';
import face18 from '/images/faces/18.jpg';
import face19 from '/images/faces/19.jpg';
import face20 from '/images/faces/20.jpg';
import face21 from '/images/faces/21.jpg';

interface ChatUserType {
    id: number;
    name: string;
    time: string;
    message: string;
    image: string;
    unreadCount: number;
    typing: boolean;
    status: 'online' | 'offline' | 'away';
    isActive: boolean;
}

interface ChatUser1Type {
    id: number;
    name: string;
    time: string;
    message: string;
    image: string;
    online: boolean;
    status: 'online' | 'offline' | 'away';
}

interface GroupChatType {
    id: number;
    name: string;
    onlineCount: number;
    avatars: string[];
    extraCount: number;
}

interface GroupChatPreviewType {
    id: number;
    name: string;
    time: string;
    sender?: string;
    message: string;
    image: string;
    online: boolean;
    unread?: boolean;
}

interface Contact {
    name: string;
    avatar?: string;
    initials?: string;
}

interface GroupedContactType {
    id: number;
    letter: string;
    contacts: Contact[];
}

export const ChatUsers: ChatUserType[] = [
    {
        id: 1,
        name: 'John Doe',
        time: '10:30 AM',
        message: "Got your email 😊, I’ll send over the details by EOD! 😄",
        image: face15,
        unreadCount: 0,
        typing: false,
        status: "online",
        isActive: false,
    },
    {
        id: 2,
        name: 'Alice Smith',
        time: '12:15 PM',
        message: 'Typing...',
        image: face2,
        unreadCount: 1,
        typing: true,
        status: "online",
        isActive: true,
    },
    {
        id: 3,
        name: 'Bob Johnson',
        time: '2:00 PM',
        message: 'Let’s schedule a call to discuss the project timeline.',
        image: face10,
        unreadCount: 3,
        typing: false,
        status: "online",
        isActive: false,
    },
    {
        id: 4,
        name: 'Emily Davis',
        time: '4:30 PM',
        message: 'The document is ready for final approval! 💹',
        image: face8,
        unreadCount: 0,
        typing: false,
        status: "online",
        isActive: false,
    },
],
    ChatUsers1: ChatUser1Type[] = [
        {
            id: 1,
            name: 'Michael Brown',
            time: '7:00 PM',
            message: 'I’ll finish the remaining tasks tomorrow.',
            image: face11,
            online: false,
            status: "offline"
        },
        {
            id: 2,
            name: 'Sarah Lee',
            time: '10:45 AM',
            message: 'Can you share the meeting notes from yesterday?',
            image: face3,
            online: false,
            status: "offline"
        },
        {
            id: 3,
            name: 'David Williams',
            time: '3:30 PM',
            message: 'I think we should revise the design before the next meeting.',
            image: face16,
            online: false,
            status: "offline"
        },
        {
            id: 4,
            name: 'Olivia Wilson',
            time: '3 days ago',
            message: 'Just wanted to confirm the new meeting time.',
            image: face4,
            online: false,
            status: "offline"
        },
        {
            id: 5,
            name: 'William Clark',
            time: '9:00 AM',
            message:
                'I’ve added the new features to the app. Let me know your thoughts.',
            image: face13,
            online: false,
            status: "offline"
        },
        {
            id: 6,
            name: 'Sophia Lewis',
            time: '5 days ago',
            message:
                'I’m looking forward to seeing the final version of the presentation.',
            image: face5,
            online: false,
            status: "offline"
        },
    ],
    GroupChats: GroupChatType[] = [
        {
            id: 1,
            name: 'Team Innovators',
            onlineCount: 4,
            avatars: [
                face2,
                face8,
                face2,
                face10,
            ],
            extraCount: 19,
        },
        {
            id: 2,
            name: 'Creative Minds Chat',
            onlineCount: 32,
            avatars: [
                face1,
                face7,
                face3,
                face9,
                face12,
            ],
            extraCount: 123,
        },
        {
            id: 3,
            name: 'Social Media Managers',
            onlineCount: 3,
            avatars: [
                face4,
                face8,
                face13,
            ],
            extraCount: 15,
        },
        {
            id: 4,
            name: 'Startup Hustlers',
            onlineCount: 5,
            avatars: [
                face1,
                face7,
                face14,
            ],
            extraCount: 28,
        },
        {
            id: 5,
            name: 'Sales Team Network',
            onlineCount: 0,
            avatars: [
                face5,
                face6,
                face12,
                face3,
            ],
            extraCount: 53,
        },
    ],
    GroupChatPreviews: GroupChatPreviewType[] = [
        {
            id: 17,
            name: 'Family Chat 😍',
            time: '10:45 AM',
            sender: 'Lily Perez',
            message: 'How are you doing today? 😊',
            image: face17,
            online: true,
        },
        {
            id: 18,
            name: 'Home Team',
            time: '4:54 PM',
            sender: 'Paul Carter',
            message: 'Let me know if you need anything else.',
            image: face18,
            online: true,
            unread: true,
        },
        {
            id: 19,
            name: 'Our Tribe 😎',
            time: '8:32 AM',
            message: 'Simon,Melissa,Amanda,Patrick,Siddique',
            image: face19,
            online: false,
        },
        {
            id: 20,
            name: 'The Circle',
            time: 'Yesterday',
            message: 'Kamalan,Subha,Ambrose,Kiara,Jackson',
            image: face20,
            online: false,
        },
        {
            id: 21,
            name: 'Family Bond',
            time: '2 Days ago',
            message: 'Subman,Rajen,Kairo,Dibasha,Alexa',
            image: face21,
            online: false,
        },
    ],
    GroupedContacts: GroupedContactType[] = [
        {
            id: 1,
            letter: 'A',
            contacts: [{ name: 'Emma Johnson', avatar: face5 }],
        },
        {
            id: 2,
            letter: 'B',
            contacts: [{ name: 'James Miller', avatar: face12 }],
        },
        {
            id: 3,
            letter: 'C',
            contacts: [{ name: 'John Smith', avatar: face14 }],
        },
        {
            id: 4,
            letter: 'D',
            contacts: [{ name: 'Robert Johnson', avatar: face9 }],
        },
        {
            id: 5,
            letter: 'E',
            contacts: [{ name: 'Olivia Smith', avatar: face7 }],
        },
        {
            id: 6,
            letter: 'J',
            contacts: [{ name: 'Paul Carter', avatar: face15 }],
        },
        {
            id: 7,
            letter: 'L',
            contacts: [{ name: 'Andrew Young', initials: 'AY' }],
        },
        {
            id: 8,
            letter: 'M',
            contacts: [{ name: 'Isabella Davis', avatar: face2 }],
        },
        {
            id: 9,
            letter: 'N',
            contacts: [{ name: 'Michael Brown', avatar: face10 }],
        },
        {
            id: 10,
            letter: 'W',
            contacts: [{ name: 'Thomas King', avatar: face16 }],
        },
    ]