import { Link, usePage } from "@inertiajs/react";
import {
  CalendarCheck,
  CalendarDays,
  LayoutGrid,
  Search,
  Shirt,
  Store,
  Tags,
} from "lucide-react";
import AppLogo from "@/components/app-logo";
import { NavMain } from "@/components/nav-main";
import { NavUser } from "@/components/nav-user";
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from "@/components/ui/sidebar";
import { dashboard } from "@/routes";
import { index as adminCategories } from "@/routes/admin/categories";
import {
  dashboard as ownerDashboard,
  setup as ownerSetup,
} from "@/routes/owner";
import { index as ownerItems } from "@/routes/owner/items";
import { index as marketplace } from "@/routes/marketplace";
import { index as myBookings } from "@/routes/bookings";
import { index as ownerBookings } from "@/routes/owner/bookings";
import type { NavItem } from "@/types";

const rentNavItems: NavItem[] = [
  {
    title: "Marketplace",
    href: marketplace(),
    icon: Search,
  },
  {
    title: "My bookings",
    href: myBookings(),
    icon: CalendarDays,
  },
  {
    title: "Dashboard",
    href: dashboard(),
    icon: LayoutGrid,
  },
];

const ownerNavItems: NavItem[] = [
  {
    title: "Shop dashboard",
    href: ownerDashboard(),
    icon: Store,
  },
  {
    title: "My items",
    href: ownerItems(),
    icon: Shirt,
  },
  {
    title: "Booking requests",
    href: ownerBookings(),
    icon: CalendarCheck,
  },
];

const becomeOwnerNavItems: NavItem[] = [
  {
    title: "Open a shop",
    href: ownerSetup(),
    icon: Store,
  },
];

const adminNavItems: NavItem[] = [
  {
    title: "Categories",
    href: adminCategories(),
    icon: Tags,
  },
];

export function AppSidebar() {
  const { isOwner, isAdmin } = usePage().props;

  return (
    <Sidebar
      collapsible="icon"
      variant="inset"
    >
      <SidebarHeader>
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton
              size="lg"
              asChild
            >
              <Link
                href={dashboard()}
                prefetch
              >
                <AppLogo />
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarHeader>

      <SidebarContent>
        <NavMain
          label="Rent"
          items={rentNavItems}
        />
        <NavMain
          label="Shop"
          items={isOwner ? ownerNavItems : becomeOwnerNavItems}
        />
        {isAdmin && adminNavItems.length > 0 && (
          <NavMain
            label="Admin"
            items={adminNavItems}
          />
        )}
      </SidebarContent>

      <SidebarFooter>
        <NavUser />
      </SidebarFooter>
    </Sidebar>
  );
}
