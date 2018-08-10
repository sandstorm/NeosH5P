import React, {PureComponent} from 'react';
import PropTypes from 'prop-types';
import {neos} from '@neos-project/neos-ui-decorators';
import {Button} from '@neos-project/react-ui-components/';
import ContentListScreen from "./ContentListScreen";
import ContentAddEditScreen from "./ContentAddEditScreen";
import ContentDisplayScreen from "./ContentDisplayScreen";

@neos(globalRegistry => ({
    secondaryEditorsRegistry: globalRegistry.get('inspector').get('secondaryEditors')
}))
export default class ContentPickerEditor extends PureComponent {
    static propTypes = {
        value: PropTypes.string,
        commit: PropTypes.func.isRequired,
        renderSecondaryInspector: PropTypes.func.isRequired
    };

    constructor(props) {
        super(props);
        this.state = {
            persistenceObjectIdentifier: "TODO",
            contentTitle: "TODO",
            libraryTitle: "TODO"
        };
        if (this.props.value) {
            this.fetchLibraryDetails(this.props.value);
        }
    }

    fetchLibraryDetails = (contentId) => {
        return fetch('http://127.0.0.1:8081/neos/service/data-source/sandstorm-neosh5p-content?contentId=' + contentId, {credentials: 'same-origin'})
        .then(response => response.json())
        .then(json => {
            console.log(json);
            this.setState({
                persistenceObjectIdentifier: json['persistenceObjectIdentifier'],
                contentTitle: json["contentTitle"],
                libraryTitle: json["libraryTitle"]
            })
        });
    };


    onContentPicked = (contentId) => {
        this.props.commit(contentId);
        this.fetchLibraryDetails(contentId);
    };

    onContentEdit = (contentId) => {
        this.fetchLibraryDetails(contentId).then(this.openContentAddEditScreen);
    };

    onContentDelete = (contentId) => {
        // TODO: delete on server, show confirmation
    };

    openContentDisplayScreen = () => {
        this.props.renderSecondaryInspector('H5P_CONTENT_DISPLAY_SCREEN', () =>
            <ContentDisplayScreen contentPersistenceObjectId={this.state.persistenceObjectIdentifier}
                                  onContentPicked={this.onContentPicked}
                                  onContentEdit={this.onContentEdit}
                                  onContentDelete={this.onContentDelete} />
        );
    };

    openContentAddEditScreen = () => {
        this.props.renderSecondaryInspector('H5P_CONTENT_ADDEDIT_SCREEN', () =>
            <ContentAddEditScreen contentPersistenceObjectId={this.state.persistenceObjectIdentifier} />
        );
    };

    openContentListScreen = () => {
        this.props.renderSecondaryInspector('H5P_CONTENT_LIST_SCREEN', () =>
            <ContentListScreen onContentPicked={this.onContentPicked}/>
        );
    };

    render() {
        return <div>
            <div>Id: {this.props.value}</div>
            <div>Title: {this.state.contentTitle}</div>
            <div>Library: {this.state.libraryTitle}</div>
            <br />
            <div className="content-picker-editor__buttons">
                <Button style="lighter" onClick={this.openContentAddEditScreen}> New </Button>
                <Button style="lighter" onClick={this.openContentListScreen}> Choose </Button>
                <Button style="lighter" isDisabled={!this.props.value} onClick={this.openContentDisplayScreen}> Show </Button>
            </div>
        </div>
    }
}
