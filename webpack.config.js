module.exports = {
    module: {
        rules: [
            {
            test: /\.js$/,
            exclude: /(node_modules)/,
            use: {
                loader: "babel-loader",
                options: {
                    presets: [["@babel/env", {
                        'targets': {
                            'browsers': ['IE 11']
                        }
                    }]],
                    sourceType: 'unambiguous',
                    plugins: ['@babel/transform-runtime','@babel/transform-async-to-generator']
                }
            }
            }
        ]
    }
};