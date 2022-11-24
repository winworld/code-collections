import React from "react";
import tw, { styled, theme } from "twin.macro";
import { useParams } from "react-router";
import { StreamChat } from "stream-chat";
import {
  Chat,
  Channel,
  ChannelHeader,
  MessageInput,
  MessageList,
  Thread,
  Window,
  ChannelList,
  CustomStyles,
  UploadsPreview,
  ChatAutoComplete,
  useMessageInputContext,
  useMessageContext,
  StreamMessage,
  MessageActionsBoxProps,
  ReactionSelector,
  ReactionsList,

} from "stream-chat-react";



import { FileUploadButton } from "react-file-utils";
import { IonIcon } from "@ionic/react";
import { chevronDownOutline, closeOutline } from "ionicons/icons";
import { useEffect, useState } from "react";

import { withDefaultLayout } from "@app/layouts/default-layout";
import useAuth from "@app/hooks/useAuth";
import { when } from "@app/styles/utils";
import { useCreateChannelMutation, useGetChatTokenQuery } from "@app/generated/graphql";

import "stream-chat-react/dist/css/index.css";

import { Button, ButtonLink } from "@app/components/form";
import send from "@app/assets/icons/send.svg";
import ellipsisH from "@app/assets/icons/ellipsis-h.svg";
import reply from "@app/assets/icons/reply.svg";
import copy from "@app/assets/icons/copy.svg";
import remove from "@app/assets/icons/delete.svg";
import edit from "@app/assets/icons/pencil-new.svg";
import userBan from "@app/assets/icons/user-ban.svg";


const chatClient = StreamChat.getInstance("pc5b2yn548th");

type IChatListingProps = {
  /**
   * The listing title
   */
  title: string;

  /**
   * The thumbnail of this listing
   */
  thumbnail: string;

  /**
   * The description of this listing
   */
  description: string;

  /**
   * The button text
   */
  buttonText: string;

  /**
   * The action to take when the button is clicked
   */
  onButtonClick: () => void;
};

type ISupportUserProps = {
  /**
   * The support user name
   */
  title: string;

  /**
   * The profile image of this user
   */
  profileImg: string;
};

const MOCK_DATA = {
  title: "Aenean tortor lectus",
  thumbnail:
    "https://images.unsplash.com/photo-1628414832152-aafba4dec0d2?ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&ixlib=rb-1.2.1&auto=format&fit=crop&w=934&q=80",
  description: "Upcoming: Oct 2 - Oct 10",
  buttonText: "Mark as picked up",
  onButtonClick: () => {
    console.log("clicked");
  },
};

const MOCK_SUPPORT_DATA = {
  title: "Gary",
  profileImg: "https://i.pinimg.com/originals/81/4a/6f/814a6f89bd1ae45233652e3e049048b1.jpg",
};

const ChatPage: React.FC = () => {
  const { user } = useAuth();
  const { listingId } = useParams<{ listingId: string }>();
  const createChannelMutation = useCreateChannelMutation();

  const query = useGetChatTokenQuery();

  const filters: any = { type: "messaging", members: { $in: [user?._id] } };
  // const filters = useMemo(() => ({ type: 'messaging', members: { $in: [user?._id] } }), [user]);
  const sort: any = [{ last_message_at: -1 }];

  const [createdChannel, setCreatedChannel] = useState(false);
  useEffect(() => {
    if (listingId && !createdChannel) {
      console.log("Creating channel");
      setCreatedChannel(true);
      createChannelMutation.mutate({ listingId });
    }
  }, [createdChannel, listingId, createChannelMutation]);

  const [connectedUser, setConnectedUser] = useState(false);
  useEffect(() => {
    (async () => {
      if (user?._id && query.data?.ChatToken?.__typename === "ChatToken") {
        await chatClient.connectUser(
          {
            id: user?._id,
            image: "https://getstream.io/random_png/?id=odd-sea-8&name=odd-sea-8",
          },
          // TODO: we need to update this to support when a token needs to be refreshed, however cutting and pasting this
          // might play nicely with the useEffect()
          // See https://getstream.io/chat/docs/node/tokens_and_authentication/?language=javascript#how-to-refresh-expired-tokens
          query.data.ChatToken.token
        );
        setConnectedUser(true);
      } else if (query.data?.ChatToken?.__typename === "GenericError") {
        console.log(query.data.ChatToken.errorMessage);
      }
    })();
  }, [user, query.data]);

  if (query.isLoading || query.isIdle || !user || connectedUser === false) {
    return <div>Loading...</div>;
  }

  if (query.isError || !query.data.ChatToken) {
    return <div>Generic Error: {JSON.stringify(query.error, null, 2)}</div>;
  }

  return (
    <div tw="w-full h-full fixed z-10 overflow-auto md:z-auto md:relative md:bg-gray-100">
      <div tw="container p-0 md:px-2">
        <ChatWrapper>
          <Chat client={chatClient} customStyles={chatStyles}>
            <ChannelListWrapper>
              <div tw="text-md hidden md:block">MESSAGES</div>
              <ChannelList filters={filters} sort={sort} />
            </ChannelListWrapper>
            <ChannelWrapper>
              {/* <Channel Input={CustomMessageInput} Message={CustomMessage}> */}
              <Channel Input={CustomMessageInput}>
                {/* <Channel> */}
                <Window>
                  <ChannelHeaderWrapper>
                    <ChannelHeader />
                  </ChannelHeaderWrapper>
                  <SupportUser
                    title={MOCK_SUPPORT_DATA.title}
                    profileImg={MOCK_SUPPORT_DATA.profileImg}
                  />
                  <ChatListingComponent
                    title={MOCK_DATA.title}
                    thumbnail={MOCK_DATA.thumbnail}
                    description={MOCK_DATA.description}
                    buttonText={MOCK_DATA.buttonText}
                    onButtonClick={MOCK_DATA.onButtonClick}
                  />
                  <MessageList messageActions={getMessageActions()} customMessageActions={customMessageActions} />
                  {/* <MessageList customMessageActions={customMessageActions}  /> */}
                  <MessageInput />
                </Window>
                <Thread />
              </Channel>
            </ChannelWrapper>
          </Chat>
        </ChatWrapper>
      </div>
    </div>
  );
};

export default withDefaultLayout(ChatPage);

const getMessageActions = () => {
 
  return ["delete", "flag", "mute", "react", "reply"];
 
};


const replyHandler = (message: StreamMessage, event: React.BaseSyntheticEvent) => {
  window.alert('Reply Action Clicked!');
};
const editMessageHandler = (message: StreamMessage, event: React.BaseSyntheticEvent) => {
  window.alert('Edit Action Clicked!');
};
const copyMessageHandler = (message: StreamMessage, event: React.BaseSyntheticEvent) => {
  window.alert('Copy Action Clicked!');
};
const blockUserHandler = (message: StreamMessage, event: React.BaseSyntheticEvent) => {
  window.alert('Block User Action Clicked!');
};
const deleteMessageHandler = (message: StreamMessage, event: React.BaseSyntheticEvent) => {
  window.alert('Delete Message Action Clicked!');
};


const customMessageActions = { 
  Reply: replyHandler,
  'Copy Message': copyMessageHandler,
  'Edit Message': editMessageHandler,
  'Block User': blockUserHandler,
  'Delete Message': deleteMessageHandler,
  
};

interface IStyleProps {
  isActive?: boolean;
}

const CustomMessageInput = () => {
  const { handleSubmit, uploadNewFiles } = useMessageInputContext();

  return (
    <div tw="flex px-4 bg-white items-center gap-2">
      <FileUploadWrapper>
        <FileUploadButton handleFiles={uploadNewFiles} />
      </FileUploadWrapper>
      <div tw="flex-grow">
        <div className="str-chat__input-flat--textarea-wrapper">
          <UploadsPreview />
          <ChatAutoComplete />
        </div>
      </div>
      <div onClick={handleSubmit} tw="hover:cursor-pointer">
        <IonIcon icon={send} tw="h-8 w-8" />
      </div>
    </div>
  );
};

const customReactions = [
  // add array of custom `ReactionEmoji` type objects here
{
    colons: ':arrow_up:',
    emoticons: [],
    id: 'arrow_up',
    name: 'Upwards Black Arrow',
    native: '⬆️',
    skin: null,
    unified: '2b06-fe0f',
  },
  {
    colons: ':arrow_down:',
    emoticons: [],
    id: 'arrow_down',
    name: 'Downwards Black Arrow',
    native: '⬇️',
    skin: null,
    unified: '2b07-fe0f',
  },
];

const CustomReactionSelector = (props: any) => {
  // add custom reaction selector component here
  return (
    <ReactionSelector
      // handleReaction={customReactionHandler}
      reactionOptions={customReactions}
    />
  )
};

const CustomReactionsList = (props: any) => {
  // add custom reactions list component here
};

const customReactionHandler = (reactionType: string, event: React.BaseSyntheticEvent) => {
  // add custom handler logic here
};

const CustomMessage = () => {
  // consume `MessageContext`
  const { message, showDetailedReactions } = useMessageContext();

  return (
    <div>
      {showDetailedReactions && (
        <ReactionSelector
          // handleReaction={customReactionHandler}
          reactionOptions={customReactions}
        />
      )}
    
      {message.latest_reactions && <ReactionsList reactionOptions={customReactions} />}
    </div>
  );
};



const SupportUser: React.FC<ISupportUserProps> = ({ title, profileImg }) => {
  const [active, setActive] = React.useState(false);
  return (
    <SupportUserWrapper onClick={() => setActive(!active)} isActive={active}>
      <div tw="h-12 w-12 bg-gray-600 rounded-full">
        <img src={profileImg} tw="h-full w-full rounded-full object-cover" />
      </div>
      <div tw="text-base relative font-bold pr-7">
        {title}
        <span tw="mt-1 absolute right-0 p-0">
          <IonIcon icon={chevronDownOutline} slot="icon-only" />
        </span>
      </div>
    </SupportUserWrapper>
  );
};

const SupportUserWrapper = styled.div<IStyleProps>`
  ${tw`flex items-center gap-5 bg-white p-4 border-b border-gray-100 hover:cursor-pointer`}
  ${(p) => when(p.isActive)} {
    ion-icon {
      -webkit-transform: rotate(-90deg);
      -ms-transform: rotate(-90deg);
      transform: rotate(-90deg);
    }

    -webkit-transition: max-height 0.5s ease-in-out;
    transition: max-height 0.5s ease-in-out;
  }
`;

const ChatListingComponent: React.FC<IChatListingProps> = ({
  title,
  thumbnail,
  description,
  buttonText,
  onButtonClick,
}) => {
  const [contextMenu, setContextMenu] = React.useState(false);
  return (
    <div tw="flex justify-between items-center bg-white p-4 border-b border-gray-100">
      <div tw="flex">
        <div tw="h-12 w-12 bg-gray-600 rounded-md">
          <img src={thumbnail} tw="h-full w-full rounded-md object-cover" />          
        </div>

        <div tw="ml-5">
          <div tw="flex">
            <div tw="text-base font-bold">{title}</div>
          </div>
          <ul tw="flex items-center justify-between">
            <li>
              <span tw="text-sm text-black">{description}</span>
            </li>
          </ul>
        </div>
      </div>
      <div tw="flex justify-end">
        <ButtonWrapper>
          <Button color="off-white" size="small" tw="p-0" onClick={onButtonClick}>
            <div tw="bg-pastel-green text-mid-green hover:text-gray-500 h-8 leading-8 -mx-4 px-4">
              <span tw="text-sm font-medium">{buttonText}</span>
            </div>
          </Button>
        </ButtonWrapper>
        <div tw="relative w-8 h-8">
          <Button
            fill="clear"
            tw="px-0 absolute top-0 -right-4"
            size="small"
            onClick={() => setContextMenu(!contextMenu)}
          >
            <IonIcon icon={ellipsisH} tw="h-4 w-6 justify-self-center" />
          </Button>
          {contextMenu && (
            <div tw="absolute top-9 right-0  w-56 shadow-lg rounded-md bg-white ring-gray-200 ring-opacity-5">
              <ul>
                <li tw="flex mx-3 py-2.5 border-b border-gray-200">
                  <ButtonLink tw="text-md truncate mr-2 text-black no-underline">
                    Menu One
                  </ButtonLink>
                </li>
                <li tw="flex mx-3 py-2">
                  <ButtonLink tw="text-md truncate text-black no-underline">Menu Two</ButtonLink>
                </li>
              </ul>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

const ChannelHeaderWrapper = styled.div`
  ${tw`md:hidden`}

  .str-chat__header-livestream {
    ${tw`min-h-full`}
  }
  .str-chat__header-livestream-left {
    ${tw`hidden`}
  }
`;

const ButtonWrapper = styled.div`
  ion-button .button-native {
    ${tw`p-0`}
  }
`;
const FileUploadWrapper = styled.div`
  ${tw`flex relative my-4 self-end`}
  .rfu-file-upload-button {
    ${tw`relative right-auto `}
    svg {
      ${tw`w-8 h-8`}
    }
  }
`;

const ChatWrapper = styled.div`
  ${tw`px-0 md:flex md:gap-8 md:px-2`}

  .str-chat__textarea textarea {
    overflow: hidden;
  }
`;

const ChannelListWrapper = styled.div`
  ${tw`flex flex-col md:w-1/3 relative`}
  .str-chat__avatar {
    ${tw`
      !w-12 !h-12
    `};

    line-height: 48px !important;

    .str-chat__avatar-fallback {
      ${tw`text-black bg-primary`}
    }
  }
  .str-chat-channel-list {
    .str-chat__channel-list-messenger__main {
      ${tw`w-full p-0`}
    }
  }
  .str-chat__channel-preview-messenger {
    ${tw`my-0 md:my-3`}
  }

  .str-chat__channel-preview-messenger--active {
    ${tw`rounded-none md:rounded-lg`}
  }

  .str-chat-channel-list--open.messaging {
    ${tw`w-5/6`}
  }
`;

const ChannelWrapper = styled.div`
  ${tw`flex md:w-2/3`}
  .str-chat {
    ${tw`h-auto max-h-full`}
  }
  .str-chat.messaging {
    ${tw`w-full h-auto max-h-full`}

    .str-chat__header-livestream {
      ${tw`shadow-none border-b border-gray-100 rounded-none`}
    }
    .str-chat__container {
      ${tw`flex w-full`}
    }

    .str-chat__main-panel {
      ${tw`p-0`}
    }
    .str-chat__list {
      .str-chat__reverse-infinite-scroll {
        ${tw`pt-2`}
      }
    }

    .str-chat__li--single,
    .str-chat__li--top,
    .str-chat__li--middle,
    .str-chat__li--bottom {
      .str-chat__message--me .str-chat__message-text-inner {
        ${tw`py-1 px-3 rounded-lg`}
      }
    }

    .str-chat__date-separator {
      ${tw`justify-center py-2`}
      .str-chat__date-separator-line {
        ${tw`hidden`}
      }
      .str-chat__date-separator-date {
        ${tw`text-xs text-white bg-gray-400 font-normal py-0.5 px-2.5 rounded`}
      }
    }

    .str-chat__message-simple-text-inner {
      ${tw`md:max-width[350px]`}
    }
  }
  .str-chat__message-actions-box{
    ${tw`width[250px]`}
  }
  .str-chat__message-actions-list{
    ${tw`w-full border border-gray-100 shadow-lg rounded-md bg-white ring-gray-200 ring-opacity-5`}
    button{
      &:not(:last-of-type){
        ${tw`border-0 border-none border-b-0`}
        li{
          ${tw`border-b border-gray-100`}
        }
      }
      &:last-child{
        ${tw`hidden`}
      }
      li{
        ${tw`bg-no-repeat`}
        // background-size:20px 20px;
        background-position:0px 6px;
      }
      &:nth-child(1) li{
        background-image:url(${reply});
      }
      &:nth-child(2) li{
        background-image:url(${copy});
      }
      &:nth-child(3) li{
        background-image:url(${edit});
      }
      &:nth-child(4) li{
        background-image:url(${userBan});
      }
      &:nth-child(5) li{
        background-image:url(${remove});
        ${tw`text-red`}
      }
    }
    li{   
      ${tw`pl-8 width[90%] margin[0 5%] text-md`}
      
    }
  } 
`;
const channelList = {
  ".str-chat__channel-preview-messenger--name": {
    color: "green",
  },
};

const chatStyles: CustomStyles = {
  "--primary-color": `${theme`colors.black`}`,
  "--lg-font": `${theme`fontSize.md`} `,
  "--md-font": `${theme`fontSize.md`} `,
  "--xs-m": `${theme`fontSize.sm`} `,
  "--main-font": "Rubik",
  "--second-font": "Rubik",
  "--grey-gainsboro": `${theme`backgroundColor.gray.100`} `,
  "--grey-whisper": `${theme`colors[pastel-green]`}`,
};
