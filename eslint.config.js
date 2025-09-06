import js from "@eslint/js";
import react from "eslint-plugin-react";
import tsParser from "@typescript-eslint/parser";

export default [
	{
		// Ignore typical build and dependency directories but allow project
		// source files like assets/js/** to be linted.
		ignores: [
			"node_modules/**",
			"vendor/**",
			"dist/**",
			"build/**",
			],
	},
	{
		files: ["**/*.{js,jsx,ts,tsx}"],
		...js.configs.recommended,
		...react.configs.flat.recommended,
		languageOptions: {
			parser: tsParser,
			ecmaVersion: "latest",
			sourceType: "module",
			parserOptions: {
				ecmaFeatures: {
					jsx: true,
				},
			},
		},
		plugins: {
			react,
		},
		settings: {
			react: {
				version: "detect",
			},
		},
	},
	];
