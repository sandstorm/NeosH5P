import React, {PureComponent} from 'react';
import PropTypes from 'prop-types';
import {neos} from '@neos-project/neos-ui-decorators';
import {Button} from '@neos-project/react-ui-components/';
import ContentAddEditScreen from "./../ContentAddEditScreen";
import ContentDisplayScreen from "./../ContentDisplayScreen";

@neos(globalRegistry => ({
    secondaryEditorsRegistry: globalRegistry.get('inspector').get('secondaryEditors')
}))
export default class ContentPickerEditor extends PureComponent {
    static propTypes = {
        value: PropTypes.string,
        commit: PropTypes.func.isRequired,
        secondaryEditorsRegistry: PropTypes.object.isRequired,
        renderSecondaryInspector: PropTypes.func.isRequired,
    };

    constructor(props) {
        super(props);
        this.state = {
            contentId: null,
            title: null
        };
        if (this.props.value) {
            this.fetchContentDetails(this.props.value);
        }
    }

    fetchContentDetails = contentId => {
        return fetch('/neos/service/data-source/sandstorm-neosh5p-content?contentId=' + contentId, {credentials: 'same-origin'})
            .then(response => response.json())
            .then(json => this.setState(json));
    };


    onContentPicked = content => {
        this.setState(content);
        this.props.commit(content.contentId);
    };

    handleDisplayContent = () => {
        this.props.renderSecondaryInspector('H5P_CONTENT_DISPLAY_SCREEN', () =>
            <ContentDisplayScreen contentPersistenceObjectId={this.state.persistenceObjectIdentifier}
                                  onContentPicked={this.onContentPicked}
                                  onContentEdit={this.onContentEdit}
                                  onContentDelete={this.onContentDelete}/>
        );
    };

    handleNewContent = () => {
        this.props.renderSecondaryInspector('H5P_CONTENT_ADDEDIT_SCREEN', () =>
            <ContentAddEditScreen contentPersistenceObjectId={this.state.persistenceObjectIdentifier}/>
        );
    };

    handleChooseContent = () => {
        const {component: ContentFullscreenEditor} = this.props.secondaryEditorsRegistry.get('Sandstorm.NeosH5P/ContentFullscreenEditor');
        this.props.renderSecondaryInspector('H5P_CONTENT_LIST_SCREEN', () =>
            <ContentFullscreenEditor
                action='index'
                currentContent={this.state}
                doNotAppendToQuery={true}
                onContentPicked={this.onContentPicked}/>
        );
    };

    render() {
        return <div>
            <p><strong>{this.props.value ? this.state.title : 'No Content selected.'}</strong></p>
            <div>
                <Button style="lighter" onClick={this.handleNewContent}>New</Button>
                <Button style="lighter" onClick={this.handleChooseContent}>Choose</Button>
                <Button style="lighter" isDisabled={!this.props.value} onClick={this.handleDisplayContent}>Edit</Button>
            </div>
        </div>
    }
}
