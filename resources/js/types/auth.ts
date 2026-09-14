export type UserRole =
    | 'member'
    | 'chapter_admin'
    | 'correspondent'
    | 'super_admin';

export type UserStatus = 'active' | 'suspended' | 'banned';

export type User = {
    id: number;
    name: string;
    email: string;
    role: UserRole;
    status: UserStatus;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
};
