import { createFileRoute } from "@tanstack/react-router";

export const Route = createFileRoute("/")({
  head: () => ({
    meta: [
      { title: "Oinklytics POS — Complete Unified System" },
      {
        name: "description",
        content:
          "Oinklytics POS: unified point-of-sale, inventory, customer, and analytics system.",
      },
      { property: "og:title", content: "Oinklytics POS" },
      {
        property: "og:description",
        content:
          "Unified POS, inventory, customer, and analytics system.",
      },
      { property: "og:type", content: "website" },
    ],
  }),
  component: Index,
});

function Index() {
  return (
    <iframe
      src="/oinklytics.html"
      title="Oinklytics POS"
      style={{
        position: "fixed",
        inset: 0,
        width: "100%",
        height: "100%",
        border: "none",
      }}
      allow="microphone; clipboard-read; clipboard-write"
    />
  );
}
