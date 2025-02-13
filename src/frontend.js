import React, { useState } from "react";
import ReactDOM from "react-dom";
import axios from "axios";
import Papa from "papaparse";
import { FaEye } from "react-icons/fa";
import { FaDownload } from "react-icons/fa";

import "./frontend.scss";

const block = document.querySelectorAll(".citation-finder-update");

block.forEach(function (el) {
  ReactDOM.render(<CitationFinder />, el);
  el.classList.remove("citation-finder-update");
});

function CitationFinder() {
  const [formData, setFormData] = useState({});
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [results, setResults] = useState(null);
  const [filename, setFilename] = useState("");
  const [viewTable, setViewTable] = useState(false);
  const [items, setItems] = useState([]);
  const [time, setTime] = useState(0);

  async function getResults(keyword) {
    try {
      setViewTable(false);
      setResults(null);
      setError(null);
      setLoading(true);
      if (!keyword || keyword === "") {
        setError("Please enter a keyword");
        setLoading(false);
        return;
      }
      const response = await axios.get(
        `${site_url.root_url}/wp-json/localwiz-enhancements/v1/citation-finder?kw=${keyword}`
      );
      if (!response.statusText === "OK") {
        console.log("Error fetching data");
        return;
      } else {
        const data = response.data;
        const items = data.tasks[0].result[0].items;
        const urls = items.map((item) => [
          item.url ? item.url : "No url found",
        ]);

        urls.unshift([formData.keyword]);

        let csv = Papa.unparse(urls);

        let csvBlob = new Blob([csv], { type: "text/csv;charset=utf-8;" });

        let csvUrl = URL.createObjectURL(csvBlob);

        let date = new Date();
        let formattedDate = `${date.getFullYear()}-${
          date.getMonth() + 1
        }-${date.getDate()}`;

        setFilename(`${formattedDate} ${formData.keyword}.csv`);
        setTime(parseFloat(data.time));
        setItems(urls);
        setResults(csvUrl);
        setLoading(false);
      }
    } catch (e) {
      setError(`Unable to fetch data: ${e.message}`);
      setLoading(false);
    }
  }

  function handleChange(e) {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value,
    });
  }

  function handleSubmit(e) {
    e.preventDefault();
    getResults(formData.keyword);
  }

  return (
    <div className="container">
      <form onSubmit={handleSubmit}>
        <div className="mb-3">
          <label class="form-label">Enter keyword here</label>
          <input
            type="text"
            name="keyword"
            className="form-control"
            placeholder="ex. Weather control"
            onChange={handleChange}
            disabled={loading}
          ></input>
        </div>
        <div className="mb-3">
          <button
            type="submit"
            className="btn btn-success w-100"
            disabled={loading}
          >
            {loading ? (
              <span
                className="spinner-border spinner-border-sm"
                role="status"
                aria-hidden="true"
              ></span>
            ) : (
              "Submit"
            )}
          </button>
        </div>
      </form>
      {error && <div className="alert alert-danger">{error}</div>}
      {results && (
        <>
          <span>
            This task took <strong>{time}</strong>{" "}
            {time === 1 ? "second" : "seconds"} to complete.
          </span>
          <hr />
          <div className="mt-3 d-flex flex-row justify-content-center align-items-center">
            <span>{filename}</span>
            <a href={results} download={filename} className="btn btn-link">
              <FaDownload />
            </a>
            <br />
            <button
              className="btn btn-link"
              onClick={(e) => {
                e.preventDefault();
                if (viewTable) {
                  setViewTable(false);
                } else {
                  setViewTable(true);
                }
              }}
            >
              <FaEye />
            </button>
          </div>
        </>
      )}
      {viewTable && (
        <div className="container">
          <table className="table mt-3">
            <thead>
              <tr>
                <th>URL</th>
              </tr>
            </thead>
            <tbody>
              {items.map((url, index) => (
                <tr key={index}>
                  <td
                    style={{
                      maxWidth: "0",
                      overflow: "hidden",
                      textOverflow: "ellipsis",
                      whiteSpace: "nowrap",
                    }}
                  >
                    <a href={url} target="_blank" rel="noreferrer">
                      {url}
                    </a>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
