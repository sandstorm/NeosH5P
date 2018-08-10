import React, {PureComponent} from 'react';
import PropTypes from 'prop-types';
import {neos} from '@neos-project/neos-ui-decorators';
import {Button} from '@neos-project/react-ui-components/';


@neos()
export default class ContentListScreen extends PureComponent {
    static PropTypes = {
        onContentPicked: PropTypes.func.isRequired,
    };

    render() {
        window.NeosH5PBrowserCallbacks = {
            contentPicked: this.props.onContentPicked
        };

        // TODO: Remove all inline styles and put them into a css file
        return <iframe src="http://127.0.0.1:8081/neosh5p/content/list" style={{width: "100%", height: "100%"}}/>
    }
}
