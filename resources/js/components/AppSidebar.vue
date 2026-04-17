<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import {
    Activity,
    BookOpen,
    Briefcase,
    Building2,
    FileText,
    Globe,
    Handshake,
    LayoutGrid,
    Paperclip,
    ReceiptIcon,
    StickyNote,
    UserPlus,
    Users,
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Users',
        href: '/users',
        icon: Users,
        children: [
            {
                title: 'Job Titles',
                href: '/job-titles',
                icon: Briefcase,
            },
        ],
    },
    {
        title: 'Activities',
        href: '/activities',
        icon: Activity,
    },
    {
        title: 'Attachments',
        href: '/attachments',
        icon: Paperclip,
    },
    {
        title: 'Companies',
        href: '/companies',
        icon: Building2,
        children: [
            {
                title: 'Company Industries',
                href: '/industries',
                icon: Globe,
            },
        ],
    },
    {
        title: 'Deals',
        href: '/deals',
        icon: Handshake,
    },
    {
        title: 'Invoices',
        href: '/invoices',
        icon: FileText,
        children: [
            {
                title: 'Invoice Items',
                href: '/invoice-items',
                icon: ReceiptIcon,
            },
        ],
    },
    {
        title: 'Leads',
        href: '/leads',
        icon: UserPlus,
    },
    {
        title: 'Learnings',
        href: '/learnings',
        icon: BookOpen,
    },
    {
        title: 'Notes',
        href: '/notes',
        icon: StickyNote
    }
];

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>