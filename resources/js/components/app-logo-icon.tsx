import type { SVGAttributes } from "react";

export default function AppLogoIcon(props: SVGAttributes<SVGElement>) {
  return (
    <svg
      {...props}
      viewBox="0 0 40 40"
      xmlns="http://www.w3.org/2000/svg"
    >
      <rect
        width="40"
        height="40"
        fill="var(--primary)"
      />
      <text
        x="20"
        y="21"
        textAnchor="middle"
        dominantBaseline="central"
        fill="var(--primary-foreground)"
        fontFamily="var(--font-brand)"
        fontWeight="900"
        fontSize="26"
        letterSpacing="-1"
      >
        f.
      </text>
    </svg>
  );
}
