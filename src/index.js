import "./index.scss";

wp.blocks.registerBlockType("localwiz-enhancements/citation-finder", {
  title: "Citation Finder",
  icon: "search",
  category: "widgets",
  edit: EditComponent,
  save: function () {
    return null;
  },
});

function EditComponent() {
  return (
    <div className="cf-edit-block">
      <h3>Citation Finder Block</h3>
    </div>
  );
}
