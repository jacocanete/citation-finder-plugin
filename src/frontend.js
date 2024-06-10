import React from "react";
import ReactDOM from "react-dom";
import { TextControl } from "@wordpress/components";
import axios from "axios";
import "./frontend.scss";

const block = document.querySelectorAll(".citation-finder-update");

block.forEach(function (el) {
  ReactDOM.render(<CitationFinder />, el);
  el.classList.remove("citation-finder-update");
});

function CitationFinder() {
  const getResults = async (keyword) => {
    try {
      const response = await axios.get(
        site_url.root_url + "/wp-json/localwiz-enhancements/v1/citation-finder"
      );
      console.log(response);
    } catch (e) {
      console.log(e);
    }
  };

  //   console.log(site_url.root_url);

  return (
    <div className="container">
      <form>
        <div className="mb-3">
          <label class="form-label">Enter keyword here</label>
          <input
            type="text"
            className="form-control"
            placeholder="ex. Weather control"
          ></input>
        </div>
        <div className="mb-3"></div>
        <button type="submit" className="btn btn-success">
          Submit
        </button>
      </form>
    </div>
  );
}
