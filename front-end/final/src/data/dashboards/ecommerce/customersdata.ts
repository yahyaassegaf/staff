import face1 from '/images/faces/1.jpg';
import face2 from '/images/faces/2.jpg';
import face3 from '/images/faces/3.jpg';
import face4 from '/images/faces/4.jpg';
import face5 from '/images/faces/5.jpg';
import face9 from '/images/faces/9.jpg';
import face10 from '/images/faces/10.jpg';
import face11 from '/images/faces/11.jpg';
import face13 from '/images/faces/13.jpg';
import face14 from '/images/faces/14.jpg';


interface CustomerType {
    id: number;
    ip: string;
    name: string;
    avatar: string;
    status: 'Active' | 'Blocked';
    joinedDate: string;
    email: string;
}


export const CustomerTable: CustomerType[] = [
    {
        id: 1,
        ip: '192.168.1.1',
        name: 'John Doe',
        avatar: face9,
        status: 'Active',
        joinedDate: 'Jan 15, 2025',
        email: 'john.doe@example.com',
    },
    {
        id: 2,
        ip: '192.168.1.2',
        name: 'Jane Smith',
        avatar: face1,
        status: 'Blocked',
        joinedDate: 'Feb 3, 2025',
        email: 'jane.smith@example.com',
    },
    {
        id: 3,
        ip: '192.168.1.3',
        name: 'Michael Brown',
        avatar: face10,
        status: 'Active',
        joinedDate: 'Mar 10, 2025',
        email: 'michael.brown@example.com',
    },
    {
        id: 4,
        ip: '192.168.1.4',
        name: 'Emily White',
        avatar: face2,
        status: 'Active',
        joinedDate: 'Mar 12, 2025',
        email: 'emily.white@example.com',
    },
    {
        id: 5,
        ip: '192.168.1.5',
        name: 'Chris Johnson',
        avatar: face11,
        status: 'Active',
        joinedDate: 'Jan 25, 2025',
        email: 'chris.johnson@example.com',
    },
    {
        id: 6,
        ip: '192.168.1.6',
        name: 'Sarah Lee',
        avatar: face3,
        status: 'Blocked',
        joinedDate: 'Feb 14, 2025',
        email: 'sarah.lee@example.com',
    },
    {
        id: 7,
        ip: '192.168.1.7',
        name: 'David Green',
        avatar: face13,
        status: 'Active',
        joinedDate: 'Mar 17, 2025',
        email: 'david.green@example.com',
    },
    {
        id: 8,
        ip: '192.168.1.8',
        name: 'Olivia Davis',
        avatar: face4,
        status: 'Active',
        joinedDate: 'Feb 22, 2025',
        email: 'olivia.davis@example.com',
    },
    {
        id: 9,
        ip: '192.168.1.9',
        name: 'James Wilson',
        avatar: face14,
        status: 'Active',
        joinedDate: 'Mar 5, 2025',
        email: 'james.wilson@example.com',
    },
    {
        id: 10,
        ip: '192.168.1.10',
        name: 'Sophia Martinez',
        avatar: face5,
        status: 'Blocked',
        joinedDate: 'Jan 30, 2025',
        email: 'sophia.martinez@example.com',
    },
];
