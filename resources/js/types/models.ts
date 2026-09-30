export type Category = {
  id: number;
  name: string;
  code: string;
  is_active: boolean;
};

export type ItemStatus =
  | "draft"
  | "active"
  | "paused"
  | "pending_review"
  | "taken_down";

export type UnitCondition = "new" | "excellent" | "good" | "fair" | "worn";

export type UnitStatus = "active" | "maintenance" | "retired";

export type ItemImage = {
  id: number;
  path: string;
  url: string;
  sort_order: number;
};

export type ItemUnit = {
  id: number;
  item_id: number;
  label: string;
  condition: UnitCondition;
  status: UnitStatus;
};

export type Item = {
  id: number;
  category_id: number;
  name: string;
  series: string | null;
  character: string | null;
  size: string | null;
  description: string | null;
  daily_rate: number;
  deposit: number;
  status: ItemStatus;
  takedown_reason: string | null;
  category?: Category;
  cover_image?: ItemImage | null;
  images?: ItemImage[];
  units?: ItemUnit[];
  units_count?: number;
};
